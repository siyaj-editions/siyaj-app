<?php

namespace App\Tests\Entity;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Genre;
use PHPUnit\Framework\TestCase;

class BookAuthorRelationTest extends TestCase
{
    public function testAddAuthorSynchronizesInverseRelation(): void
    {
        $book = (new Book())->setTitle('Livre test')->setSlug('livre-test')->setPriceCents(1000);
        $author = (new Author())->setName('Auteur test')->setSlug('auteur-test');

        $book->addAuthor($author);

        self::assertCount(1, $book->getAuthors());
        self::assertTrue($book->getAuthors()->contains($author));
        self::assertTrue($author->getBooks()->contains($book));
    }

    public function testRemoveAuthorSynchronizesInverseRelation(): void
    {
        $book = (new Book())->setTitle('Livre test')->setSlug('livre-test')->setPriceCents(1000);
        $author = (new Author())->setName('Auteur test')->setSlug('auteur-test');

        $book->addAuthor($author);
        $book->removeAuthor($author);

        self::assertCount(0, $book->getAuthors());
        self::assertFalse($author->getBooks()->contains($book));
    }

    public function testGetAuthorsNamesReturnsCommaSeparatedNames(): void
    {
        $book = (new Book())->setTitle('Livre test')->setSlug('livre-test')->setPriceCents(1000);
        $book->addAuthor((new Author())->setName('Victor Hugo')->setSlug('victor-hugo'));
        $book->addAuthor((new Author())->setName('George Sand')->setSlug('george-sand'));

        self::assertSame('Victor Hugo, George Sand', $book->getAuthorsNames());
    }

    public function testAddGenreSynchronizesInverseRelation(): void
    {
        $book = (new Book())->setTitle('Livre test')->setSlug('livre-test')->setPriceCents(1000);
        $genre = (new Genre())->setName('Roman')->setSlug('roman');

        $book->addGenre($genre);

        self::assertCount(1, $book->getGenres());
        self::assertTrue($book->getGenres()->contains($genre));
        self::assertTrue($genre->getBooks()->contains($book));
    }

    public function testRemoveGenreSynchronizesInverseRelation(): void
    {
        $book = (new Book())->setTitle('Livre test')->setSlug('livre-test')->setPriceCents(1000);
        $genre = (new Genre())->setName('Roman')->setSlug('roman');

        $book->addGenre($genre);
        $book->removeGenre($genre);

        self::assertCount(0, $book->getGenres());
        self::assertFalse($genre->getBooks()->contains($book));
    }

    public function testGetGenresNamesReturnsCommaSeparatedNames(): void
    {
        $book = (new Book())->setTitle('Livre test')->setSlug('livre-test')->setPriceCents(1000);
        $book->addGenre((new Genre())->setName('Roman')->setSlug('roman'));
        $book->addGenre((new Genre())->setName('Essai')->setSlug('essai'));

        self::assertSame('Roman, Essai', $book->getGenresNames());
    }
}
