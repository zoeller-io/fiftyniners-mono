<?php

namespace App\Command;

use App\Entity\MailingHistory;
use App\Entity\MailingTemplate;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;
use Symfony\Component\Uid\Uuid;
use Twig\Environment;

#[AsCommand(
    name: 'app:email:send',
    description: 'Send email message to a recipient list.'
)]
class SendEmailCommand extends Command
{
    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly EntityManagerInterface $entityManager,
        private readonly Environment $twig,
        private readonly MailerInterface $mailer,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('template', InputArgument::REQUIRED, 'The name of email template to send.')
            ->addOption('filter', 'f', InputOption::VALUE_OPTIONAL, 'An expression to filter recipients.')
            ->addOption('season', 's', InputOption::VALUE_OPTIONAL, 'The start year of season to handle.')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Run command without persisting the data.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Arguments
        $templateName = $input->getArgument('template');
        // Options
        $recipientFilter = $input->getOption('filter');
        $isDryRun = $input->getOption('dry-run');

        // Calculate current season
        $seasonStartYear = $input->getOption('season');
        $today = new \DateTimeImmutable('today');
        if (null === $seasonStartYear) {
            $seasonStartYear = (int)$today->format('Y');
            if ((int)$today->format('m') < 7) {
                $seasonStartYear--;
            }
        } else {
            $seasonStartYear = (int)$seasonStartYear;
        }
        $season = sprintf('%d/%d', $seasonStartYear, $seasonStartYear - 2000 + 1);

        $emailInterval = 2; // in seconds
        $from = Address::create('EFC Fiftyniners <mail@59ers.eu>');

        // Find mailing template
        $templateRepository = $this->entityManager->getRepository(MailingTemplate::class);
        $templates = $templateRepository->findAll();
        $mailingTemplate = null;
        foreach ($templates as $template) {
            if ($template->getName() === $templateName) {
                $mailingTemplate = $template;
                break;
            }
        }
        if (null === $mailingTemplate) {
            $output->writeln(sprintf('Template [%s] not found.', $templateName));
            return Command::FAILURE;
        }

        // Add signature to text and html body
        $textSignature = $this->twig->render('emails/_signature.txt.twig');
        $textTemplate = $this->twig->createTemplate($mailingTemplate->getTextBody() . $textSignature);
        $htmlSignature = $this->twig->render('emails/_signature.html.twig');
        $htmlTemplate = $this->twig->createTemplate($mailingTemplate->getHtmlBody() . $htmlSignature);

        // Iterate members and send emails
        $memberRepository = $this->entityManager->getRepository(Member::class);
        $historyRepository = $this->entityManager->getRepository(MailingHistory::class);
        $members = $memberRepository->findAll();
        $i = 0;
        foreach ($members as $member) {
            if (
                !$member->isDisabled()
                && (
                    null === $recipientFilter
                    || in_array($recipientFilter, $member->getTags(), true)
                )
            ) {
                $to = Address::create(sprintf(
                    '%s %s <%s>',
                    $member->getFirstName(),
                    $member->getLastName(),
                    $member->getEmailAddress()
                ));
                $mailings = $historyRepository->findBy(['recipient' => $to->toString()]);
                $alreadySent = false;
                foreach ($mailings as $mailing) {
                    if (in_array($recipientFilter, $mailing->getRecipientFilters(), true)) {
                        $alreadySent = true;
                        break;
                    }
                }
                if ($alreadySent) {
                    $output->writeln(sprintf('Email [%s] with filter [%s] already sent to [%s].', $templateName, $recipientFilter, $to->toString()));
                    continue;
                }
                $seasonTicket = null;
                foreach ($member->getSeasonTickets() as $ticket) {
                    if ($ticket->getSeason() === $seasonStartYear) {
                        $seasonTicket = $ticket;
                        break;
                    }
                }

                $subject = $mailingTemplate->getSubject();
                $context = [
                    'member' => $member,
                    'season' => $season,
                    'seasonStartYear' => $seasonStartYear,
                    'ticket' => $seasonTicket
                ];
                $textBody = $textTemplate->render($context);
                $htmlBody = $htmlTemplate->render($context);
                $email = (new TemplatedEmail())
                    ->from($from)
                    ->to($to)
                    ->subject($subject)
                    ->text($textBody)
                    ->html($htmlBody)
                    ->locale('de');

                // Attach files if exists
                $documentsDir = $this->kernel->getProjectDir() . '/documents';
                $attachmentPrefix = $templateName;
                $finder = new Finder();
                foreach ($finder->in($documentsDir) as $file) {
                    if (str_starts_with($file->getFilename(), $attachmentPrefix)) {
                        // @todo Handle additional content types (pdf etc.)
                        if ($file->getExtension() === 'jpg') {
                            $contentType = 'image/jpeg';
                        }
                        $email->addPart(new DataPart(new File($file->getPathname()), $file->getFilename(), $contentType));
                    }
                }

                // Send email
                if (!$isDryRun) {
                    $this->mailer->send($email);
                }
                $output->writeln(sprintf("Email [%s] with filter [%s] sent to [%s].", $templateName, $recipientFilter, $member->getEmailAddress()));

                // Archive email
                $mailing = new MailingHistory();
                $mailing->setMember($member);
                $mailing->setRecipient($to->toString());
                $mailing->setRecipientFilters(null !== $recipientFilter ? [$recipientFilter] : []);
                $mailing->setUuid(Uuid::v4());
                $mailing->setSender($from->toString());
                $mailing->setSubject($subject);
                $mailing->setTextBody($textBody);
                $mailing->setHtmlBody($htmlBody);
                $mailing->setSentAt(new \DateTimeImmutable());
                if (!$isDryRun) {
                    $this->entityManager->persist($mailing);
                    $this->entityManager->flush();
                }
                if ($this->kernel->getEnvironment() === 'prod') {
                    sleep($emailInterval);
                }
                $i++;
            }
        }
        $output->writeln(sprintf('Total of [%d] emails sent.', $i));

        return Command::SUCCESS;
    }
}