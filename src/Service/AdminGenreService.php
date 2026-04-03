<?php

namespace App\Service;

use App\Entity\Genre;
use App\Repository\GenreRepository;
use Doctrine\ORM\EntityManagerInterface;

class AdminGenreService
{
    public function __construct(
        private readonly GenreRepository $genreRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @return Genre[]
     */
    public function listGenres(): array
    {
        return $this->genreRepository->findBy([], ['name' => 'ASC']);
    }

    public function createGenre(Genre $genre): void
    {
        $this->entityManager->persist($genre);
        $this->entityManager->flush();
    }

    public function updateGenre(): void
    {
        $this->entityManager->flush();
    }

    public function deleteGenre(Genre $genre): void
    {
        $this->entityManager->remove($genre);
        $this->entityManager->flush();
    }
}
