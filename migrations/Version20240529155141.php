<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240529155141 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Delete userId from theme entity';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE theme DROP CONSTRAINT fk_9775e708a76ed395');
        $this->addSql('DROP INDEX idx_9775e708a76ed395');
        $this->addSql('ALTER TABLE theme DROP user_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE theme ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE theme ADD CONSTRAINT fk_9775e708a76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_9775e708a76ed395 ON theme (user_id)');
    }
}
