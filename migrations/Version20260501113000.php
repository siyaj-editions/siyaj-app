<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260501113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow deleting books while preserving order item snapshots and clean book relations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE book_genre DROP CONSTRAINT FK_6F03620116A2B381');
        $this->addSql('ALTER TABLE book_genre ADD CONSTRAINT FK_6F03620116A2B381 FOREIGN KEY (book_id) REFERENCES book (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE book_genre DROP CONSTRAINT FK_6F03620116A2B381');
        $this->addSql('ALTER TABLE book_genre ADD CONSTRAINT FK_6F03620116A2B381 FOREIGN KEY (book_id) REFERENCES book (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
