<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240627220725 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE subscription_option (id INT NOT NULL, subscription_id INT NOT NULL, name VARCHAR(255) NOT NULL, is_active BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_937018379A1887DC ON subscription_option (subscription_id)');
        $this->addSql('ALTER TABLE subscription_option ADD CONSTRAINT FK_937018379A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE subscription_option_id_seq CASCADE');
        $this->addSql('ALTER TABLE subscription_option DROP CONSTRAINT FK_937018379A1887DC');
        $this->addSql('DROP TABLE subscription_option');
    }
}
