<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240616204713 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update Customer entity by replacing created_by field by owner';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer DROP CONSTRAINT fk_81398e09b03a8386');
        $this->addSql('DROP INDEX idx_81398e09b03a8386');
        $this->addSql('ALTER TABLE customer ADD owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE customer DROP created_by_id');
        $this->addSql('ALTER TABLE customer ALTER company_id DROP NOT NULL');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E097E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_81398E097E3C61F9 ON customer (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE customer DROP CONSTRAINT FK_81398E097E3C61F9');
        $this->addSql('DROP INDEX IDX_81398E097E3C61F9');
        $this->addSql('ALTER TABLE customer ADD created_by_id INT NOT NULL');
        $this->addSql('ALTER TABLE customer DROP owner_id');
        $this->addSql('ALTER TABLE customer ALTER company_id SET NOT NULL');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT fk_81398e09b03a8386 FOREIGN KEY (created_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_81398e09b03a8386 ON customer (created_by_id)');
    }
}
