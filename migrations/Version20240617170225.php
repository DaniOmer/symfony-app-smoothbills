<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240617170225 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adding ManyToOne relation between GraphicChart and Font entity.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE graphic_chart ADD title_font_id INT NOT NULL');
        $this->addSql('ALTER TABLE graphic_chart ADD content_font_id INT NOT NULL');
        $this->addSql('ALTER TABLE graphic_chart DROP title_font');
        $this->addSql('ALTER TABLE graphic_chart DROP content_font');
        $this->addSql('ALTER TABLE graphic_chart ADD CONSTRAINT FK_9F7BE0CE958BC092 FOREIGN KEY (title_font_id) REFERENCES font (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE graphic_chart ADD CONSTRAINT FK_9F7BE0CE8FF2058 FOREIGN KEY (content_font_id) REFERENCES font (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_9F7BE0CE958BC092 ON graphic_chart (title_font_id)');
        $this->addSql('CREATE INDEX IDX_9F7BE0CE8FF2058 ON graphic_chart (content_font_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE graphic_chart DROP CONSTRAINT FK_9F7BE0CE958BC092');
        $this->addSql('ALTER TABLE graphic_chart DROP CONSTRAINT FK_9F7BE0CE8FF2058');
        $this->addSql('DROP INDEX IDX_9F7BE0CE958BC092');
        $this->addSql('DROP INDEX IDX_9F7BE0CE8FF2058');
        $this->addSql('ALTER TABLE graphic_chart ADD title_font VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE graphic_chart ADD content_font VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE graphic_chart DROP title_font_id');
        $this->addSql('ALTER TABLE graphic_chart DROP content_font_id');
    }
}
