<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240613182817 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adding timestamp and uuid field to Customer entity.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer ADD uid UUID NOT NULL');
        $this->addSql('ALTER TABLE customer ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE customer ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('COMMENT ON COLUMN customer.uid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_81398E09539B0606 ON customer (uid)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_81398E09539B0606');
        $this->addSql('ALTER TABLE customer DROP uid');
        $this->addSql('ALTER TABLE customer DROP created_at');
        $this->addSql('ALTER TABLE customer DROP updated_at');
    }
}
