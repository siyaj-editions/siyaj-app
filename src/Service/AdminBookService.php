<?php

namespace App\Service;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Genre;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Repository\GenreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;

class AdminBookService
{
    public function __construct(
        private readonly BookRepository $bookRepository,
        private readonly AuthorRepository $authorRepository,
        private readonly GenreRepository $genreRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly BookCoverStorage $bookCoverStorage,
    ) {
    }

    /**
     * @return Book[]
     */
    public function listBooks(): array
    {
        return $this->bookRepository->findBy([], ['createdAt' => 'DESC']);
    }

    public function createBook(Book $book, array $authorNames = [], array $genreNames = [], ?UploadedFile $coverImageFile = null): void
    {
        $this->syncCoverImage($book, $coverImageFile);
        $this->syncAuthors($book, $authorNames);
        $this->syncGenres($book, $genreNames);
        $this->entityManager->persist($book);
        $this->entityManager->flush();
    }

    public function updateBook(Book $book, array $authorNames = [], array $genreNames = [], ?UploadedFile $coverImageFile = null): void
    {
        $this->syncCoverImage($book, $coverImageFile);
        $this->syncAuthors($book, $authorNames);
        $this->syncGenres($book, $genreNames);
        $this->entityManager->flush();
    }

    public function deleteBook(Book $book): void
    {
        $this->bookCoverStorage->delete($book->getCoverImage());
        $this->entityManager->remove($book);
        $this->entityManager->flush();
    }

    /**
     * @return string[]
     */
    public function listGenres(): array
    {
        return array_values(array_filter(array_map(
            static fn (Genre $genre): ?string => $genre->getName(),
            $this->genreRepository->findBy([], ['name' => 'ASC'])
        )));
    }

    /**
     * @return string[]
     */
    public function listAuthorNames(): array
    {
        return array_values(array_filter(array_map(
            static fn (Author $author): ?string => $author->getName(),
            $this->authorRepository->findBy([], ['name' => 'ASC'])
        )));
    }

    /**
     * @param string[] $genreNames
     */
    private function syncGenres(Book $book, array $genreNames): void
    {
        $normalizedNames = [];
        foreach ($genreNames as $genreName) {
            if (!is_string($genreName)) {
                continue;
            }

            $trimmedName = trim($genreName);
            if ($trimmedName === '') {
                continue;
            }

            $normalizedNames[$this->normalizeKey($trimmedName)] = $trimmedName;
        }

        foreach ($book->getGenres()->toArray() as $existingGenre) {
            if (!isset($normalizedNames[$this->normalizeKey((string) $existingGenre->getName())])) {
                $book->removeGenre($existingGenre);
            }
        }

        foreach ($normalizedNames as $genreName) {
            $book->addGenre($this->resolveGenre($genreName));
        }
    }

    /**
     * @param string[] $authorNames
     */
    private function syncAuthors(Book $book, array $authorNames): void
    {
        $normalizedNames = [];
        foreach ($authorNames as $authorName) {
            if (!is_string($authorName)) {
                continue;
            }

            $trimmedName = trim($authorName);
            if ($trimmedName === '') {
                continue;
            }

            $normalizedNames[$this->normalizeKey($trimmedName)] = $trimmedName;
        }

        foreach ($book->getAuthors()->toArray() as $existingAuthor) {
            if (!isset($normalizedNames[$this->normalizeKey((string) $existingAuthor->getName())])) {
                $book->removeAuthor($existingAuthor);
            }
        }

        foreach ($normalizedNames as $authorName) {
            $book->addAuthor($this->resolveAuthor($authorName));
        }
    }

    private function resolveAuthor(string $authorName): Author
    {
        $slug = (new AsciiSlugger())->slug($authorName)->lower()->toString();
        $author = $this->authorRepository->findBySlug($slug);

        if ($author instanceof Author) {
            return $author;
        }

        $author = (new Author())->setName($authorName);
        $this->entityManager->persist($author);

        return $author;
    }

    private function normalizeKey(string $value): string
    {
        return (new AsciiSlugger())->slug($value)->lower()->toString();
    }

    private function resolveGenre(string $genreName): Genre
    {
        $slug = $this->normalizeKey($genreName);
        $genre = $this->genreRepository->findBySlug($slug);

        if ($genre instanceof Genre) {
            return $genre;
        }

        $genre = (new Genre())->setName($genreName);
        $this->entityManager->persist($genre);

        return $genre;
    }

    private function syncCoverImage(Book $book, ?UploadedFile $coverImageFile): void
    {
        if (!$coverImageFile instanceof UploadedFile) {
            return;
        }

        $previousCoverImage = $book->getCoverImage();
        $book->setCoverImage($this->bookCoverStorage->upload($coverImageFile));
        $this->bookCoverStorage->delete($previousCoverImage);
    }
}
