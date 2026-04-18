<?php

namespace App\Tests\Service;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Genre;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Repository\GenreRepository;
use App\Service\AdminBookService;
use App\Service\BookCoverStorage;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class AdminBookServiceTest extends TestCase
{
    public function testCreateBookCreatesMissingAuthorsAndDeduplicatesNames(): void
    {
        $bookRepository = $this->createMock(BookRepository::class);
        $authorRepository = $this->createMock(AuthorRepository::class);
        $genreRepository = $this->createMock(GenreRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $bookCoverStorage = $this->createMock(BookCoverStorage::class);
        $service = new AdminBookService($bookRepository, $authorRepository, $genreRepository, $entityManager, $bookCoverStorage);
        $book = new Book();

        $existingAuthor = (new Author())->setName('Maryse Conde');

        $authorRepository
            ->expects(self::exactly(2))
            ->method('findBySlug')
            ->willReturnMap([
                ['maryse-conde', $existingAuthor],
                ['patrick-chamoiseau', null],
            ]);

        $entityManager
            ->expects(self::exactly(3))
            ->method('persist')
            ->willReturnCallback(static function (object $entity) use ($book): void {
                self::assertTrue($entity === $book || $entity instanceof Author || $entity instanceof Genre);
            });

        $entityManager
            ->expects(self::once())
            ->method('flush');

        $genreRepository
            ->expects(self::once())
            ->method('findBySlug')
            ->with('roman')
            ->willReturn(null);

        $service->createBook($book, ['Maryse Conde', ' Patrick Chamoiseau ', 'Patrick Chamoiseau'], ['Roman']);

        self::assertCount(2, $book->getAuthors());
        self::assertCount(1, $book->getGenres());
        self::assertSame(['Maryse Conde', 'Patrick Chamoiseau'], array_map(
            static fn (Author $author): string => (string) $author->getName(),
            $book->getAuthors()->toArray()
        ));
        self::assertSame(['Roman'], array_map(
            static fn (Genre $genre): string => (string) $genre->getName(),
            $book->getGenres()->toArray()
        ));
    }

    public function testUpdateBookRemovesAuthorsMissingFromSubmittedList(): void
    {
        $bookRepository = $this->createMock(BookRepository::class);
        $authorRepository = $this->createMock(AuthorRepository::class);
        $genreRepository = $this->createMock(GenreRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $bookCoverStorage = $this->createMock(BookCoverStorage::class);
        $service = new AdminBookService($bookRepository, $authorRepository, $genreRepository, $entityManager, $bookCoverStorage);
        $book = new Book();
        $firstAuthor = (new Author())->setName('Auteur 1');
        $secondAuthor = (new Author())->setName('Auteur 2');
        $firstGenre = (new Genre())->setName('Roman');
        $secondGenre = (new Genre())->setName('Poesie');

        $book->addAuthor($firstAuthor);
        $book->addAuthor($secondAuthor);
        $book->addGenre($firstGenre);
        $book->addGenre($secondGenre);

        $authorRepository
            ->expects(self::once())
            ->method('findBySlug')
            ->with('auteur-1')
            ->willReturn($firstAuthor);

        $entityManager
            ->expects(self::once())
            ->method('flush');

        $genreRepository
            ->expects(self::once())
            ->method('findBySlug')
            ->with('roman')
            ->willReturn($firstGenre);

        $service->updateBook($book, ['Auteur 1'], ['Roman']);

        self::assertCount(1, $book->getAuthors());
        self::assertTrue($book->getAuthors()->contains($firstAuthor));
        self::assertFalse($book->getAuthors()->contains($secondAuthor));
        self::assertCount(1, $book->getGenres());
        self::assertTrue($book->getGenres()->contains($firstGenre));
        self::assertFalse($book->getGenres()->contains($secondGenre));
    }
}
