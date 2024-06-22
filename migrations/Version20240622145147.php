<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240622145147 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop invitation sequence and table, modify customer and invoice tables by adding and dropping constraints, and create necessary indexes.';

    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE invitation_id_seq CASCADE');
        $this->addSql('ALTER TABLE invitation DROP CONSTRAINT fk_f11d61a2979b1ad6');
        $this->addSql('ALTER TABLE invitation DROP CONSTRAINT fk_f11d61a27e3c61f9');
        $this->addSql('DROP TABLE invitation');
        $this->addSql('ALTER TABLE customer ADD owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E097E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_81398E097E3C61F9 ON customer (owner_id)');
        $this->addSql('DROP INDEX uniq_90651744c036f84f');
        $this->addSql('ALTER TABLE invoice ADD invoice_status_id INT NOT NULL');
        $this->addSql('ALTER TABLE invoice DROP date');
        $this->addSql('ALTER TABLE invoice DROP invoice_status');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744E58F121 FOREIGN KEY (invoice_status_id) REFERENCES invoice_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_90651744E58F121 ON invoice (invoice_status_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE invitation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE invitation (id INT NOT NULL, company_id INT NOT NULL, owner_id INT NOT NULL, email VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, expire_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, role VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_f11d61a27e3c61f9 ON invitation (owner_id)');
        $this->addSql('CREATE INDEX idx_f11d61a2979b1ad6 ON invitation (company_id)');
        $this->addSql('COMMENT ON COLUMN invitation.expire_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT fk_f11d61a2979b1ad6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT fk_f11d61a27e3c61f9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT FK_90651744E58F121');
        $this->addSql('DROP INDEX IDX_90651744E58F121');
        $this->addSql('ALTER TABLE invoice ADD date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE invoice ADD invoice_status VARCHAR(45) NOT NULL');
        $this->addSql('ALTER TABLE invoice DROP invoice_status_id');
        $this->addSql('CREATE UNIQUE INDEX uniq_90651744c036f84f ON invoice (invoice_status)');
        $this->addSql('ALTER TABLE customer DROP CONSTRAINT FK_81398E097E3C61F9');
        $this->addSql('DROP INDEX IDX_81398E097E3C61F9');
        $this->addSql('ALTER TABLE customer DROP owner_id');
    }
}
