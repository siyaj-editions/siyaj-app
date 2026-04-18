<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260417141000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename manuscript URL column to manuscript path for uploaded PDF storage';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE manuscript_submission RENAME COLUMN manuscript_url TO manuscript_path');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE manuscript_submission RENAME COLUMN manuscript_path TO manuscript_url');
    }
}
