<?php

namespace App\Tests\Entity;

use App\Entity\Author;
use App\Entity\Book;
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
}
