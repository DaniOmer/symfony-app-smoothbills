<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240620072443 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add uid to service table';
    }

    public function up(Schema $schema): void
    {

        $this->addSql('ALTER TABLE service ADD uid UUID NOT NULL');
        $this->addSql('COMMENT ON COLUMN service.uid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E19D9AD2539B0606 ON service (uid)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE service DROP uid');
    }
}