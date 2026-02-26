<?php

namespace App\Repository;

use App\Entity\ManuscriptSubmission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ManuscriptSubmission>
 */
class ManuscriptSubmissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ManuscriptSubmission::class);
    }

    /**
     * @return ManuscriptSubmission[]
     */
    public function findLatest(int $limit = 100): array
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countUnread(): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.isReadByAdmin = :isReadByAdmin')
            ->setParameter('isReadByAdmin', false)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
