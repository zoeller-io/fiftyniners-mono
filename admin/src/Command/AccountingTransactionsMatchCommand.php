<?php

namespace App\Command;

use App\Entity\FinancialLiability;
use App\Entity\FinancialTransaction;
use App\Repository\FinancialLiabilityRepository;
use App\Repository\FinancialTransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'accounting:transactions:match',
    description: 'Try to match transactions with liabilities.'
)]
class AccountingTransactionsMatchCommand extends Command
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
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Run command without persisting the data.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Options
        $isDryRun = $input->getOption('dry-run');

        /** @var FinancialLiabilityRepository $liabilityRepository */
        $liabilityRepository = $this->entityManager->getRepository(FinancialLiability::class);
        /** @var FinancialTransactionRepository $memberRepository */
        $transactionRepository = $this->entityManager->getRepository(FinancialTransaction::class);

        $liabilities = $liabilityRepository->findBy(['paidAt' => null]);
        $matched = 0;
        foreach ($liabilities as $liability) {
            $memberTransactions = $transactionRepository->findBy(
                ['liability' => null, 'member' => $liability->getMember()]
            );
            foreach ($memberTransactions as $transaction) {
                if (
                    $transaction->getAmount() === $liability->getAmount()
                    && (
                        strtolower($transaction->getReference()) === strtolower($liability->getReason())
                        || str_contains(strtolower($transaction->getReason()), strtolower($liability->getReason()))
                    )
                ) {
                    $updatedAt = new \DateTimeImmutable();
                    $transaction->setLiability($liability);
                    $transaction->setUpdatedAt($updatedAt);
                    $liability->setPaidAt($transaction->getPaidAt());
                    $liability->setUpdatedAt($updatedAt);
                    if (!$isDryRun) {
                        $this->entityManager->persist($transaction);
                        $this->entityManager->persist($liability);
                        $this->entityManager->flush();
                    }
                    $matched++;
                }
            }
        }
        $output->writeln(sprintf("<info>[%d] transactions successfully matched.</info>", $matched));

        return Command::SUCCESS;
    }
}