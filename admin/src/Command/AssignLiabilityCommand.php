<?php

namespace App\Command;

use App\Entity\FinancialLiability;
use App\Entity\Member;
use App\Repository\MemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'accounting:liability:assign',
    description: 'Assign new liability to one or multiple members.'
)]
class AssignLiabilityCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addOption('amount', 'a', InputOption::VALUE_REQUIRED, 'Amount of liability as integer.')
            ->addOption('type', 't', InputOption::VALUE_REQUIRED, 'Type of liability.')
            ->addOption('reason', 'r', InputOption::VALUE_REQUIRED, 'Reason of liability.')
            ->addOption('member', 'm', InputOption::VALUE_OPTIONAL, 'Member ID to which the liability is assigned.')
            ->addOption('tag', null, InputOption::VALUE_OPTIONAL, 'Tag to which the liability is assigned.')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Run command without persisting the data.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Options
        $amount = (int)$input->getOption('amount');
        $type = $input->getOption('type');
        $reason = $input->getOption('reason');

        $memberId = $input->getOption('member');
        $tag = $input->getOption('tag');
        if (null === $memberId && null === $tag) {
            $output->writeln("<error>Member ID or tag is mandatory.</error>");
            return Command::INVALID;
        }
        $isDryRun = $input->getOption('dry-run');

        /** @var MemberRepository $memberRepository */
        $memberRepository = $this->entityManager->getRepository(Member::class);

        $members = [];
        if (null !== $memberId) {
            $members = $memberRepository->findBy(['id' => $memberId]);
        } else {
            $members = $memberRepository->findByTag($tag);
        }

        $dueAt = (new \DateTimeImmutable('today +13 days'))->setTime(23, 59, 59);
        $assigned = 0;
        foreach ($members as $member) {
            $liability = new FinancialLiability();
            $liability->setAmount($amount);
            $liability->setType($type);
            $liability->setReason($reason);
            $liability->setMember($member);
            $liability->setDueAt($dueAt);
            $liability->setTags([$tag]);
            $liability->setCreatedAt(new \DateTimeImmutable());
            if (!$isDryRun) {
                $this->entityManager->persist($liability);
            }
            $assigned++;
        }
        if (!$isDryRun) {
            $this->entityManager->flush();
        }
        $output->writeln(sprintf("<info>[%d] liabilities successfully assigned.</info>", $assigned));

        return Command::SUCCESS;
    }
}