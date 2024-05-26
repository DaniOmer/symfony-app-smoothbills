<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240525131136 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE address_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE company_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE font_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE legal_form_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE role_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE theme_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE address (id INT NOT NULL, company_id INT NOT NULL, zipcode VARCHAR(10) NOT NULL, city VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, longitude NUMERIC(10, 8) DEFAULT NULL, latitude NUMERIC(10, 8) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D4E6F81979B1AD6 ON address (company_id)');
        $this->addSql('COMMENT ON COLUMN address.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE company (id INT NOT NULL, legal_form_id INT DEFAULT NULL, denomination VARCHAR(255) NOT NULL, siren VARCHAR(255) NOT NULL, siret VARCHAR(255) NOT NULL, tva_number VARCHAR(255) NOT NULL, rcs_number VARCHAR(255) NOT NULL, phone_number VARCHAR(65) NOT NULL, mail VARCHAR(255) NOT NULL, creation_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, registered_social INT NOT NULL, sector VARCHAR(255) NOT NULL, logo VARCHAR(255) DEFAULT NULL, signing VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4FBF094F98CD0513 ON company (legal_form_id)');
        $this->addSql('COMMENT ON COLUMN company.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE font (id INT NOT NULL, name VARCHAR(120) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE legal_form (id INT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE role (id INT NOT NULL, name VARCHAR(45) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE theme (id INT NOT NULL, title_font_id INT NOT NULL, content_font_id INT NOT NULL, subtitle_font_id INT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, primary_color VARCHAR(45) NOT NULL, secondary_color VARCHAR(45) NOT NULL, tertiary_color VARCHAR(45) NOT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9775E708958BC092 ON theme (title_font_id)');
        $this->addSql('CREATE INDEX IDX_9775E7088FF2058 ON theme (content_font_id)');
        $this->addSql('CREATE INDEX IDX_9775E708D882E50D ON theme (subtitle_font_id)');
        $this->addSql('CREATE INDEX IDX_9775E708A76ED395 ON theme (user_id)');
        $this->addSql('COMMENT ON COLUMN theme.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, owner_id INT NOT NULL, role_id INT NOT NULL, company_id INT DEFAULT NULL, first_name VARCHAR(60) NOT NULL, last_name VARCHAR(100) NOT NULL, mail VARCHAR(255) NOT NULL, password VARCHAR(128) NOT NULL, google_auth_id VARCHAR(255) DEFAULT NULL, is_mail_validated BOOLEAN DEFAULT NULL, avatar VARCHAR(255) DEFAULT NULL, job_title VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8D93D6497E3C61F9 ON "user" (owner_id)');
        $this->addSql('CREATE INDEX IDX_8D93D649D60322AC ON "user" (role_id)');
        $this->addSql('CREATE INDEX IDX_8D93D649979B1AD6 ON "user" (company_id)');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F81979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094F98CD0513 FOREIGN KEY (legal_form_id) REFERENCES legal_form (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE theme ADD CONSTRAINT FK_9775E708958BC092 FOREIGN KEY (title_font_id) REFERENCES font (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE theme ADD CONSTRAINT FK_9775E7088FF2058 FOREIGN KEY (content_font_id) REFERENCES font (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE theme ADD CONSTRAINT FK_9775E708D882E50D FOREIGN KEY (subtitle_font_id) REFERENCES font (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE theme ADD CONSTRAINT FK_9775E708A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D6497E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649D60322AC FOREIGN KEY (role_id) REFERENCES role (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE address_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE company_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE font_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE legal_form_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE role_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE theme_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('ALTER TABLE address DROP CONSTRAINT FK_D4E6F81979B1AD6');
        $this->addSql('ALTER TABLE company DROP CONSTRAINT FK_4FBF094F98CD0513');
        $this->addSql('ALTER TABLE theme DROP CONSTRAINT FK_9775E708958BC092');
        $this->addSql('ALTER TABLE theme DROP CONSTRAINT FK_9775E7088FF2058');
        $this->addSql('ALTER TABLE theme DROP CONSTRAINT FK_9775E708D882E50D');
        $this->addSql('ALTER TABLE theme DROP CONSTRAINT FK_9775E708A76ED395');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D6497E3C61F9');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649D60322AC');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649979B1AD6');
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE font');
        $this->addSql('DROP TABLE legal_form');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE theme');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
