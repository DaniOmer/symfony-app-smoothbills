<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240701171150 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add quotation number to quotation table';
    }

    public function up(Schema $schema): void
    {
        // // this up() migration is auto-generated, please modify it to your needs
        // $this->addSql('ALTER TABLE quotation ADD quotation_number VARCHAR(14) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // // this down() migration is auto-generated, please modify it to your needs
        // $this->addSql('CREATE SCHEMA public');
        // $this->addSql('ALTER TABLE quotation DROP quotation_number');
    }
}