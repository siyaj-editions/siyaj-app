<?php

namespace App\Service;

use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;

class AdminBookService
{
    public function __construct(
        private readonly BookRepository $bookRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @return Book[]
     */
    public function listBooks(): array
    {
        return $this->bookRepository->findBy([], ['createdAt' => 'DESC']);
    }

    public function createBook(Book $book): void
    {
        $this->entityManager->persist($book);
        $this->entityManager->flush();
    }

    public function updateBook(): void
    {
        $this->entityManager->flush();
    }

    public function deleteBook(Book $book): void
    {
        $this->entityManager->remove($book);
        $this->entityManager->flush();
    }
}
