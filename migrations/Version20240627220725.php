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
        $this->addSql('CREATE SEQUENCE invitation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE subscription_option_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE invitation (id INT NOT NULL, company_id INT NOT NULL, owner_id INT NOT NULL, email VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, expire_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, role VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F11D61A2979B1AD6 ON invitation (company_id)');
        $this->addSql('CREATE INDEX IDX_F11D61A27E3C61F9 ON invitation (owner_id)');
        $this->addSql('COMMENT ON COLUMN invitation.expire_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE subscription_option (id INT NOT NULL, subscription_id INT NOT NULL, name VARCHAR(255) NOT NULL, is_active BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_937018379A1887DC ON subscription_option (subscription_id)');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A27E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subscription_option ADD CONSTRAINT FK_937018379A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE customer DROP CONSTRAINT fk_81398e097e3c61f9');
        $this->addSql('ALTER TABLE customer DROP owner_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE invitation_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE subscription_option_id_seq CASCADE');
        $this->addSql('ALTER TABLE invitation DROP CONSTRAINT FK_F11D61A2979B1AD6');
        $this->addSql('ALTER TABLE invitation DROP CONSTRAINT FK_F11D61A27E3C61F9');
        $this->addSql('ALTER TABLE subscription_option DROP CONSTRAINT FK_937018379A1887DC');
        $this->addSql('DROP TABLE invitation');
        $this->addSql('DROP TABLE subscription_option');
        $this->addSql('ALTER TABLE customer ADD owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT fk_81398e097e3c61f9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
