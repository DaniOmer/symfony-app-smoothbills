<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240701172302 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE one_time_payment ALTER payment_date DROP NOT NULL');
        $this->addSql('ALTER TABLE recurring_payment ALTER payment_date DROP NOT NULL');
        $this->addSql('ALTER TABLE recurring_payment ALTER start_date DROP NOT NULL');
        $this->addSql('ALTER TABLE recurring_payment ALTER end_date DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE recurring_payment ALTER payment_date SET NOT NULL');
        $this->addSql('ALTER TABLE recurring_payment ALTER start_date SET NOT NULL');
        $this->addSql('ALTER TABLE recurring_payment ALTER end_date SET NOT NULL');
        $this->addSql('ALTER TABLE one_time_payment ALTER payment_date SET NOT NULL');
    }
}
