<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240616191302 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add uid field as unique identifier for Quotation entity';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_474a8db9d17f50a6');
        $this->addSql('ALTER TABLE quotation ADD uid UUID NOT NULL');
        $this->addSql('ALTER TABLE quotation DROP uuid');
        $this->addSql('COMMENT ON COLUMN quotation.uid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_474A8DB9539B0606 ON quotation (uid)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_474A8DB9539B0606');
        $this->addSql('ALTER TABLE quotation ADD uuid VARCHAR(45) NOT NULL');
        $this->addSql('ALTER TABLE quotation DROP uid');
        $this->addSql('CREATE UNIQUE INDEX uniq_474a8db9d17f50a6 ON quotation (uuid)');
    }
}
