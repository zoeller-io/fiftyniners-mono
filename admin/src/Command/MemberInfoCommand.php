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
    name: 'app:member:info',
    description: 'Get infos about members.'
)]
class MemberInfoCommand extends Command
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
            ->addArgument('entity', InputArgument::REQUIRED, 'The name of entity to get info of.')
            ->addOption('season', 's', InputOption::VALUE_OPTIONAL, 'The start year of season to handle.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entity = $input->getArgument('entity');
        $seasonStartYear = $input->getOption('season');

        // Calculate current season
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

        // Iterate members
        $memberRepository = $this->entityManager->getRepository(Member::class);
        $members = $memberRepository->findAll();
        $i = 0;
        $tags = [];
        foreach ($members as $member) {
            foreach ($member->getTags() as $tag) {
                if (!in_array($tag, $tags, true)) {
                    $tags[$tag][] = $member->getShortName();
                }
            }
            $i++;
        }
        $output->writeln(sprintf('Total of [%d] members and [%d] tags.', $i, count($tags)));

        ksort($tags, SORT_STRING);
        if ("tags" === $entity) {
            foreach ($tags as $tag => $members) {
                $line = sprintf("%s [%d]: ", $tag, count($members));
                foreach ($members as $member) {
                    $line .= sprintf("%s, ", $member);
                }
                $line = substr($line, 0, -2);
                $output->writeln($line);
            }
        }

        return Command::SUCCESS;
    }
}