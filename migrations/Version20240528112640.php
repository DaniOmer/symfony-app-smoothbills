<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240528112640 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ext_translations (id SERIAL NOT NULL, locale VARCHAR(8) NOT NULL, object_class VARCHAR(191) NOT NULL, field VARCHAR(32) NOT NULL, foreign_key VARCHAR(64) NOT NULL, content TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX translations_lookup_idx ON ext_translations (locale, object_class, foreign_key)');
        $this->addSql('CREATE INDEX general_translations_lookup_idx ON ext_translations (object_class, foreign_key)');
        $this->addSql('CREATE UNIQUE INDEX lookup_unique_idx ON ext_translations (locale, object_class, field, foreign_key)');
        $this->addSql('ALTER TABLE theme ADD sidebar_position VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE theme ADD bg_color VARCHAR(45) NOT NULL');
        $this->addSql('ALTER TABLE theme ALTER user_id DROP NOT NULL');
        $this->addSql('ALTER TABLE theme ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN theme.created_at IS NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE ext_translations');
        $this->addSql('ALTER TABLE theme DROP sidebar_position');
        $this->addSql('ALTER TABLE theme DROP bg_color');
        $this->addSql('ALTER TABLE theme ALTER user_id SET NOT NULL');
        $this->addSql('ALTER TABLE theme ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN theme.created_at IS \'(DC2Type:datetime_immutable)\'');
    }
}
