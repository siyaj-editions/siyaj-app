<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260403160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add default address flag';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE address ADD is_default BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('UPDATE address SET is_default = true WHERE id IN (
            SELECT MIN(a2.id) FROM address a2 GROUP BY a2.user_id
        )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE address DROP is_default');
    }
}
