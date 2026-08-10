<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260810121549 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD subscription_plan VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD is_active BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD last_login_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');

        /*
        * Initialise les comptes déjà existants.
        */
        $this->addSql('UPDATE "user" SET subscription_plan = \'free\' WHERE subscription_plan IS NULL');
        $this->addSql('UPDATE "user" SET is_active = TRUE WHERE is_active IS NULL');
        $this->addSql('UPDATE "user" SET created_at = CURRENT_TIMESTAMP WHERE created_at IS NULL');

        /*
        * Les trois propriétés sont obligatoires dans l'entité.
        */
        $this->addSql('ALTER TABLE "user" ALTER subscription_plan SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER is_active SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER created_at SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" DROP subscription_plan');
        $this->addSql('ALTER TABLE "user" DROP is_active');
        $this->addSql('ALTER TABLE "user" DROP created_at');
        $this->addSql('ALTER TABLE "user" DROP last_login_at');
    }
}
