<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260403173000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create genre entity and migrate existing book genres';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE genre (id SERIAL NOT NULL, name VARCHAR(120) NOT NULL, slug VARCHAR(120) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5F983441989D9B62 ON genre (slug)');
        $this->addSql('CREATE TABLE book_genre (book_id INT NOT NULL, genre_id INT NOT NULL, PRIMARY KEY(book_id, genre_id))');
        $this->addSql('CREATE INDEX IDX_6F03620116A2B381 ON book_genre (book_id)');
        $this->addSql('CREATE INDEX IDX_6F0362014296D31F ON book_genre (genre_id)');
        $this->addSql('ALTER TABLE book_genre ADD CONSTRAINT FK_6F03620116A2B381 FOREIGN KEY (book_id) REFERENCES book (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE book_genre ADD CONSTRAINT FK_6F0362014296D31F FOREIGN KEY (genre_id) REFERENCES genre (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql("INSERT INTO genre (name, slug)
            SELECT DISTINCT genre, LOWER(REGEXP_REPLACE(unaccent(genre), '[^a-zA-Z0-9]+', '-', 'g'))
            FROM book
            WHERE genre IS NOT NULL AND genre != ''");
        $this->addSql("INSERT INTO book_genre (book_id, genre_id)
            SELECT b.id, g.id
            FROM book b
            INNER JOIN genre g ON g.name = b.genre
            WHERE b.genre IS NOT NULL AND b.genre != ''");
        $this->addSql('ALTER TABLE book DROP genre');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE book ADD genre VARCHAR(120) DEFAULT NULL');
        $this->addSql("UPDATE book b
            SET genre = sub.name
            FROM (
                SELECT bg.book_id, MIN(g.name) AS name
                FROM book_genre bg
                INNER JOIN genre g ON g.id = bg.genre_id
                GROUP BY bg.book_id
            ) sub
            WHERE b.id = sub.book_id");
        $this->addSql('ALTER TABLE book_genre DROP CONSTRAINT FK_6F03620116A2B381');
        $this->addSql('ALTER TABLE book_genre DROP CONSTRAINT FK_6F0362014296D31F');
        $this->addSql('DROP TABLE book_genre');
        $this->addSql('DROP TABLE genre');
    }
}
