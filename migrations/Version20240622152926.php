<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240622152926 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Removing uid and date fields from Invoice entity.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_90651744d17f50a6');
        $this->addSql('ALTER TABLE invoice DROP date');
        $this->addSql('ALTER TABLE invoice DROP uuid');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE invoice ADD date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE invoice ADD uuid VARCHAR(45) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_90651744d17f50a6 ON invoice (uuid)');
    }
}
