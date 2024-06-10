<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240607214852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'remove company_id and add customer_id in table address';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE address ADD customer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE address ALTER company_id DROP NOT NULL');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F819395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D4E6F819395C3F3 ON address (customer_id)');
        $this->addSql('ALTER TABLE customer DROP CONSTRAINT fk_81398e09f5b7af75');
        $this->addSql('DROP INDEX idx_81398e09f5b7af75');
        $this->addSql('ALTER TABLE customer DROP address_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE address DROP CONSTRAINT FK_D4E6F819395C3F3');
        $this->addSql('DROP INDEX UNIQ_D4E6F819395C3F3');
        $this->addSql('ALTER TABLE address DROP customer_id');
        $this->addSql('ALTER TABLE address ALTER company_id SET NOT NULL');
        $this->addSql('ALTER TABLE customer ADD address_id INT NOT NULL');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT fk_81398e09f5b7af75 FOREIGN KEY (address_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_81398e09f5b7af75 ON customer (address_id)');
    }
}
