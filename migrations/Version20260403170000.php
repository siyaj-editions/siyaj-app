<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260403170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add genre field to book';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE book ADD genre VARCHAR(120) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE book DROP genre');
    }
}
