<?php

namespace App\Repository;

use App\Entity\PasswordResetCode;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasswordResetCode>
 */
class PasswordResetCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordResetCode::class);
    }

    public function findLatestActiveCodeForUser(User $user): ?PasswordResetCode
    {
        return $this->createQueryBuilder('prc')
            ->andWhere('prc.user = :user')
            ->andWhere('prc.usedAt IS NULL')
            ->orderBy('prc.createdAt', 'DESC')
            ->setMaxResults(1)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
