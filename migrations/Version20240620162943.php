<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240620162943 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove owner from Customer entity';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer DROP CONSTRAINT fk_81398e097e3c61f9');
        $this->addSql('DROP INDEX idx_81398e097e3c61f9');
        $this->addSql('ALTER TABLE customer DROP owner_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE customer ADD owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT fk_81398e097e3c61f9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_81398e097e3c61f9 ON customer (owner_id)');
    }
}
