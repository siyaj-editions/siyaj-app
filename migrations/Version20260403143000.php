<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260403143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add shipping snapshots to orders';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "order" ADD items_subtotal_cents INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE "order" ADD shipping_cost_cents INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE "order" ADD shipping_zone VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE "order" ADD shipping_zone_label VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE "order" ADD shipping_method VARCHAR(30) DEFAULT NULL');
        $this->addSql('ALTER TABLE "order" ADD shipping_method_label VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE "order" ADD shipping_delay_label VARCHAR(120) DEFAULT NULL');
        $this->addSql('UPDATE "order" SET items_subtotal_cents = total_cents, shipping_cost_cents = 0 WHERE items_subtotal_cents = 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "order" DROP items_subtotal_cents');
        $this->addSql('ALTER TABLE "order" DROP shipping_cost_cents');
        $this->addSql('ALTER TABLE "order" DROP shipping_zone');
        $this->addSql('ALTER TABLE "order" DROP shipping_zone_label');
        $this->addSql('ALTER TABLE "order" DROP shipping_method');
        $this->addSql('ALTER TABLE "order" DROP shipping_method_label');
        $this->addSql('ALTER TABLE "order" DROP shipping_delay_label');
    }
}
