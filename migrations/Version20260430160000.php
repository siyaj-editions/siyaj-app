<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260430160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add shipping status and tracking number to orders';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "order" ADD send_status VARCHAR(255) DEFAULT \'processing\' NOT NULL');
        $this->addSql('ALTER TABLE "order" ADD tracking_number VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "order" DROP send_status');
        $this->addSql('ALTER TABLE "order" DROP tracking_number');
    }
}
