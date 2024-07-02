<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240607144945 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new entity Customer';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE customer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE customer (id INT NOT NULL, address_id INT NOT NULL, company_id INT NOT NULL, created_by_id INT NOT NULL, name VARCHAR(255) NOT NULL, mail VARCHAR(45) DEFAULT NULL, phone VARCHAR(45) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_81398E09F5B7AF75 ON customer (address_id)');
        $this->addSql('CREATE INDEX IDX_81398E09979B1AD6 ON customer (company_id)');
        $this->addSql('CREATE INDEX IDX_81398E09B03A8386 ON customer (created_by_id)');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09B03A8386 FOREIGN KEY (created_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE customer_id_seq CASCADE');
        $this->addSql('ALTER TABLE customer DROP CONSTRAINT FK_81398E09F5B7AF75');
        $this->addSql('ALTER TABLE customer DROP CONSTRAINT FK_81398E09979B1AD6');
        $this->addSql('ALTER TABLE customer DROP CONSTRAINT FK_81398E09B03A8386');
        $this->addSql('DROP TABLE customer');
    }
}
