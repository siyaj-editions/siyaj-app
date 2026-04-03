<?php

namespace App\Repository;

use App\Entity\Genre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Genre>
 */
class GenreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Genre::class);
    }

    public function findBySlug(string $slug): ?Genre
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Genre[]
     */
    public function findActiveGenres(): array
    {
        return $this->createQueryBuilder('g')
            ->innerJoin('g.books', 'b')
            ->andWhere('b.isActive = :active')
            ->setParameter('active', true)
            ->distinct()
            ->orderBy('g.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
