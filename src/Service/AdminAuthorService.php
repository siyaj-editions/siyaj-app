<?php

namespace App\Service;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;

class AdminAuthorService
{
    public function __construct(
        private readonly AuthorRepository $authorRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @return Author[]
     */
    public function listAuthors(): array
    {
        return $this->authorRepository->findAll();
    }

    public function createAuthor(Author $author): void
    {
        $this->entityManager->persist($author);
        $this->entityManager->flush();
    }

    public function updateAuthor(): void
    {
        $this->entityManager->flush();
    }

    public function deleteAuthor(Author $author): void
    {
        $this->entityManager->remove($author);
        $this->entityManager->flush();
    }
}
