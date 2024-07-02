<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240702071208 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT FK_90651744B4EA4E60');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744B4EA4E60 FOREIGN KEY (quotation_id) REFERENCES quotation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE one_time_payment ALTER payment_date DROP NOT NULL');
        $this->addSql('ALTER TABLE payment DROP CONSTRAINT FK_6D28840D2989F1FD');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D2989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quotation ADD quotation_number VARCHAR(14) NOT NULL');
        $this->addSql('ALTER TABLE quotation_has_service DROP CONSTRAINT FK_BE88A2BDED5CA9E6');
        $this->addSql('ALTER TABLE quotation_has_service DROP CONSTRAINT FK_BE88A2BDB4EA4E60');
        $this->addSql('ALTER TABLE quotation_has_service ADD CONSTRAINT FK_BE88A2BDED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quotation_has_service ADD CONSTRAINT FK_BE88A2BDB4EA4E60 FOREIGN KEY (quotation_id) REFERENCES quotation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE recurring_payment ALTER payment_date DROP NOT NULL');
        $this->addSql('ALTER TABLE recurring_payment ALTER start_date DROP NOT NULL');
        $this->addSql('ALTER TABLE recurring_payment ALTER end_date DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE quotation DROP quotation_number');
        $this->addSql('ALTER TABLE quotation_has_service DROP CONSTRAINT fk_be88a2bded5ca9e6');
        $this->addSql('ALTER TABLE quotation_has_service DROP CONSTRAINT fk_be88a2bdb4ea4e60');
        $this->addSql('ALTER TABLE quotation_has_service ADD CONSTRAINT fk_be88a2bded5ca9e6 FOREIGN KEY (service_id) REFERENCES service (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quotation_has_service ADD CONSTRAINT fk_be88a2bdb4ea4e60 FOREIGN KEY (quotation_id) REFERENCES quotation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE payment DROP CONSTRAINT fk_6d28840d2989f1fd');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT fk_6d28840d2989f1fd FOREIGN KEY (invoice_id) REFERENCES invoice (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE recurring_payment ALTER payment_date SET NOT NULL');
        $this->addSql('ALTER TABLE recurring_payment ALTER start_date SET NOT NULL');
        $this->addSql('ALTER TABLE recurring_payment ALTER end_date SET NOT NULL');
        $this->addSql('ALTER TABLE one_time_payment ALTER payment_date SET NOT NULL');
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT fk_90651744b4ea4e60');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT fk_90651744b4ea4e60 FOREIGN KEY (quotation_id) REFERENCES quotation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
