<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240616155510 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adding uid field to Company entity';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company ADD uid UUID NOT NULL');
        $this->addSql('COMMENT ON COLUMN company.uid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4FBF094F539B0606 ON company (uid)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_4FBF094F539B0606');
        $this->addSql('ALTER TABLE company DROP uid');
    }
}
