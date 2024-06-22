<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240620211749 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'remove quotation_id from QuotationHasService';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quotation_has_service DROP CONSTRAINT fk_be88a2bdb4ea4e60');
        $this->addSql('DROP INDEX idx_be88a2bdb4ea4e60');
        $this->addSql('ALTER TABLE quotation_has_service DROP quotation_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE quotation_has_service ADD quotation_id INT NOT NULL');
        $this->addSql('ALTER TABLE quotation_has_service ADD CONSTRAINT fk_be88a2bdb4ea4e60 FOREIGN KEY (quotation_id) REFERENCES quotation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_be88a2bdb4ea4e60 ON quotation_has_service (quotation_id)');
    }
}
