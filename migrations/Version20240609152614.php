<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240609152614 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new entity Quotation, QuotationHasService, QuotationStatus';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE quotation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE quotation_has_service_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE quotation_status_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE quotation (id INT NOT NULL, quotation_status_id INT NOT NULL, company_id INT NOT NULL, customer_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, type VARCHAR(255) NOT NULL, uuid VARCHAR(45) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_474A8DB9D17F50A6 ON quotation (uuid)');
        $this->addSql('CREATE INDEX IDX_474A8DB9FA637399 ON quotation (quotation_status_id)');
        $this->addSql('CREATE INDEX IDX_474A8DB9979B1AD6 ON quotation (company_id)');
        $this->addSql('CREATE INDEX IDX_474A8DB99395C3F3 ON quotation (customer_id)');
        $this->addSql('CREATE TABLE quotation_has_service (id INT NOT NULL, quotation_id INT NOT NULL, service_id INT NOT NULL, price_without_tax NUMERIC(10, 2) NOT NULL, price_with_tax NUMERIC(10, 2) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, quantity INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BE88A2BDB4EA4E60 ON quotation_has_service (quotation_id)');
        $this->addSql('CREATE INDEX IDX_BE88A2BDED5CA9E6 ON quotation_has_service (service_id)');
        $this->addSql('CREATE TABLE quotation_status (id INT NOT NULL, name VARCHAR(45) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE quotation ADD CONSTRAINT FK_474A8DB9FA637399 FOREIGN KEY (quotation_status_id) REFERENCES quotation_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quotation ADD CONSTRAINT FK_474A8DB9979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quotation ADD CONSTRAINT FK_474A8DB99395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quotation_has_service ADD CONSTRAINT FK_BE88A2BDB4EA4E60 FOREIGN KEY (quotation_id) REFERENCES quotation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quotation_has_service ADD CONSTRAINT FK_BE88A2BDED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE quotation_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE quotation_has_service_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE quotation_status_id_seq CASCADE');
        $this->addSql('ALTER TABLE quotation DROP CONSTRAINT FK_474A8DB9FA637399');
        $this->addSql('ALTER TABLE quotation DROP CONSTRAINT FK_474A8DB9979B1AD6');
        $this->addSql('ALTER TABLE quotation DROP CONSTRAINT FK_474A8DB99395C3F3');
        $this->addSql('ALTER TABLE quotation_has_service DROP CONSTRAINT FK_BE88A2BDB4EA4E60');
        $this->addSql('ALTER TABLE quotation_has_service DROP CONSTRAINT FK_BE88A2BDED5CA9E6');
        $this->addSql('DROP TABLE quotation');
        $this->addSql('DROP TABLE quotation_has_service');
        $this->addSql('DROP TABLE quotation_status');
    }
}