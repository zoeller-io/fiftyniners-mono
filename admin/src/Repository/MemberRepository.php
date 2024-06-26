<?php

namespace App\Repository;

use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Member>
 */
class MemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    //    /**
    //     * @return Member[] Returns an array of Member objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    public function findByTag(string $tag): array
    {
        $result = $this->createQueryBuilder('m')
            ->andWhere('m.tags LIKE :tag')
            ->setParameter('tag', '%' . $tag . '%')
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function findOneByBankAccountOwner(string $ownerExpr): ?Member
    {
        $ownerExpr = $this->convertNameExpression($ownerExpr);
        $result = $this->createQueryBuilder('m')
            ->andWhere('m.bankAccountName = :bankAccountName')
            ->setParameter('bankAccountName', $ownerExpr)
            ->getQuery()
            ->getOneOrNullResult();
        if (null !== $result) {
            return $result;
        }

        $names = explode(', ', $ownerExpr);
        if (1 === count($names)) {
            $names = explode(' ', $ownerExpr);
            $lastName = $names[count($names) - 1];
            $firstNames = array_slice($names, 0, -1);
        } else {
            $lastName = $names[0];
            $firstNames = explode(' ', $names[1]);
        }

        $needles = [];
        $map = ['ae' => 'ä', 'oe' => 'ö', 'ue' => 'ü', 'ss' => 'ß'];
        $needles[] = ['firstName' => $firstNames[0], 'middleName' => null, 'lastName' => $lastName];
        if (
            $this->hasKeysFromMap($firstNames[0], $map)
            || $this->hasKeysFromMap($lastName, $map)
        ) {
            $needles[] = [
                'firstName' => $this->replaceParts($firstNames[0], $map),
                'middleName' => null,
                'lastName' => $this->replaceParts($lastName, $map)
            ];
        }
        if (2 === count($firstNames)) {
            $needles[] = ['firstName' => $firstNames[0], 'middleName' => $firstNames[1], 'lastName' => $lastName];
            $needles[] = ['firstName' => $firstNames[1], 'middleName' => $firstNames[0], 'lastName' => $lastName];
            if (
                $this->hasKeysFromMap($firstNames[0], $map)
                || $this->hasKeysFromMap($firstNames[1], $map)
                || $this->hasKeysFromMap($lastName, $map)
            ) {
                $needles[] = [
                    'firstName' => $this->replaceParts($firstNames[0], $map),
                    'middleName' => $this->replaceParts($firstNames[1], $map),
                    'lastName' => $this->replaceParts($lastName, $map)
                ];
            }
        }

        $result = null;
        foreach ($needles as $needle) {
            $qb = $this->createQueryBuilder('m')
                ->andWhere('m.firstName = :firstName')
                ->setParameter('firstName', $needle['firstName'])
                ->andWhere('m.lastName = :lastName')
                ->setParameter('lastName', $needle['lastName']);
            if (null !== $needle['middleName']) {
                $qb
                    ->andWhere('m.middleName = :middleName')
                    ->setParameter('middleName', $needle['middleName']);
            }
            $result = $qb->getQuery()
                ->getOneOrNullResult();
            if (null !== $result) {
                return $result;
            }
        }

        return null;
    }

    function convertNameExpression($name) {
        // Split the string into an array of words
        $words = explode(' ', $name);

        // Iterate over each word
        foreach ($words as &$word) {
            // Split the word into segments by dash '-'
            $segments = explode('-', $word);

            // Capitalize each segment
            foreach ($segments as &$segment) {
                $segment = ucfirst(strtolower($segment));
            }

            // Join the segments back with a dash '-'
            $word = implode('-', $segments);
        }

        // Join the words back with a space ' ' and return
        return implode(' ', $words);
    }

    function replaceParts(string $word, array $map): string
    {
        foreach ($map as $search => $replace) {
            $word = str_replace($search, $replace, $word);
        }

        return $word;
    }

    function hasKeysFromMap(string $word, array $map): bool {
        foreach ($map as $key => $value) {
            if (str_contains($word, $key)) {
                return true;
            }
        }

        return false;
    }
}
