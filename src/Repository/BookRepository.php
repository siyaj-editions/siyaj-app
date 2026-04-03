<?php

namespace App\Repository;

use App\Entity\Book;
use App\Enum\BookFormat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function findActiveBooks(): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('b.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findBySlug(string $slug): ?Book
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.authors', 'a')
            ->leftJoin('b.genres', 'g')
            ->addSelect('a')
            ->addSelect('g')
            ->andWhere('b.slug = :slug')
            ->andWhere('b.isActive = :active')
            ->setParameter('slug', $slug)
            ->setParameter('active', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveBooksWithFilters(?string $search = null, ?int $authorId = null, ?BookFormat $format = null, ?string $genre = null): array
    {
        return $this->createActiveBooksQueryBuilder($search, $authorId, $format, $genre)
            ->getQuery()
            ->getResult();
    }

    public function createActiveBooksQueryBuilder(?string $search = null, ?int $authorId = null, ?BookFormat $format = null, ?string $genre = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('b.authors', 'a')
            ->leftJoin('b.genres', 'g')
            ->addSelect('a')
            ->addSelect('g')
            ->andWhere('b.isActive = :active')
            ->setParameter('active', true)
            ->distinct();

        if ($search) {
            // Recherche insensible à la casse et aux accents (PostgreSQL)
            $qb->andWhere('LOWER(unaccent(b.title)) LIKE LOWER(unaccent(:search)) OR b.isbn LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($authorId) {
            $qb->andWhere('a.id = :authorId')
                ->setParameter('authorId', $authorId);
        }

        if ($format) {
            $qb->andWhere('b.format = :format')
                ->setParameter('format', $format);
        }

        if ($genre) {
            $qb->andWhere('g.name = :genre')
                ->setParameter('genre', $genre);
        }

        return $qb->orderBy('b.publishedAt', 'DESC');
    }

    public function save(Book $book, bool $flush = false): void
    {
        $this->getEntityManager()->persist($book);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Book $book, bool $flush = false): void
    {
        $this->getEntityManager()->remove($book);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
