<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240623145039 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add uid, created_at and updated_at in entity Payment';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment ADD uid UUID NOT NULL');
        $this->addSql('ALTER TABLE payment ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE payment ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('COMMENT ON COLUMN payment.uid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6D28840D539B0606 ON payment (uid)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_6D28840D539B0606');
        $this->addSql('ALTER TABLE payment DROP uid');
        $this->addSql('ALTER TABLE payment DROP created_at');
        $this->addSql('ALTER TABLE payment DROP updated_at');
    }
}
