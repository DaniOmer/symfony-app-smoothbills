<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240620212656 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add quotation_id in entity QuotationHasService to get method getQuotationHasServices()';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quotation_has_service ADD quotation_id INT NOT NULL');
        $this->addSql('ALTER TABLE quotation_has_service ADD CONSTRAINT FK_BE88A2BDB4EA4E60 FOREIGN KEY (quotation_id) REFERENCES quotation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_BE88A2BDB4EA4E60 ON quotation_has_service (quotation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE quotation_has_service DROP CONSTRAINT FK_BE88A2BDB4EA4E60');
        $this->addSql('DROP INDEX IDX_BE88A2BDB4EA4E60');
        $this->addSql('ALTER TABLE quotation_has_service DROP quotation_id');
    }
}
