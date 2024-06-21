<?php

namespace App\Command;

use App\Entity\FinancialTransaction;
use App\Entity\Member;
use App\Repository\FinancialTransactionRepository;
use App\Repository\MemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'accounting:transactions:import',
    description: 'Import and process weekly CSV export from bank account.'
)]
class AccountingTransactionsImportCommand extends Command
{
    public function __construct(
        private readonly KernelInterface $kernel,
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

        /** @var MemberRepository $memberRepository */
        $memberRepository = $this->entityManager->getRepository(Member::class);
        /** @var FinancialTransactionRepository $memberRepository */
        $transactionRepository = $this->entityManager->getRepository(FinancialTransaction::class);

        // Read CSV files in /tmp
        $documentsDir = $this->kernel->getProjectDir() . '/tmp';
        $fileNamePrefix = 'Umsaetze_DE41513900000066587800_';
        $finder = new Finder();
        $start = new \DateTimeImmutable('2024-06-01 00:00:00');
        $skipped = 0;
        $imported = 0;
        foreach ($finder->in($documentsDir) as $file) {
            if ('csv' === $file->getExtension() && str_starts_with($file->getFilename(), $fileNamePrefix)) {
                $rows = $this->readCsvFile($file->getPathname());
                if (count($rows) <= 1) {
                    $output->writeln("<error>The CSV file doesn't have enough rows.</error>");
                    continue;
                }
                array_shift($rows); // Remove header row
                $rows = array_reverse($rows); // Reverse rows to have the oldest transaction at top
                for ($i = 0; $i < count($rows) - 1; $i++) {
                    $row = $rows[$i];

                    $paidAt = (\DateTimeImmutable::createFromFormat('d.m.Y', $row[4]))
                        ->setTime(0, 0, 0);

                    // Skip 'old' transactions
                    if ($paidAt < $start) {
                        $skipped++;
                        continue;
                    }

                    // Skip not 'incoming' transactions
                    if ('Gutschrift' !== $row[9]) {
                        $skipped++;
                        continue;
                    }

                    // Parse owner expression to find member
                    $ownerExpr = $row[6];
                    $member = $memberRepository->findOneByBankAccountOwner($ownerExpr);

                    $amount = (float)$row[11] * 100;
                    if (0.0 === $amount) {
                        continue;
                    }

                    $reason = trim($row[10]);

                    // Check if transaction already exist
                    $transaction = $transactionRepository->findOneBy(
                        [
                            'member' => $member,
                            'method' => 'bank_transfer',
                            'amount' => $amount,
                            'paidAt' => $paidAt,
                            'reason' => $reason,
                        ]
                    );
                    if (null !== $transaction) {
                        $skipped++;
                        continue;
                    }

                    $transaction = new FinancialTransaction();
                    $transaction
                        ->setMember($member)
                        ->setMethod('bank_transfer')
                        ->setOwner($ownerExpr)
                        ->setAmount($amount)
                        ->setPaidAt($paidAt)
                        ->setReason($reason)
                        ->setCreatedAt(new \DateTimeImmutable())
                    ;
                    if (!$isDryRun) {
                        $this->entityManager->persist($transaction);
                        $this->entityManager->flush();
                    }
                    $imported++;

                    if (null === $member) {
                        $output->writeln(sprintf("<error>No member found by expression [%s]. Please manually map transaction [%d].</error>", $ownerExpr, $transaction->getId()));
                    }
                }
            }
        }
        $output->writeln(sprintf("<info>[%d] transactions successfully imported. [%d] rows skipped.</info>", $imported, $skipped));

        return Command::SUCCESS;
    }

    private function readCsvFile(string $filePath): array
    {
        $rows = [];
        if (($handle = fopen($filePath, 'rb')) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ';')) !== FALSE) {
                $rows[] = $data;
            }
            fclose($handle);
        }
        return $rows;
    }
}