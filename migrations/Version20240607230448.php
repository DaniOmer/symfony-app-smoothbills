<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240607230448 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'remove company_id and customer_id from Address and create relations between Address and Customer, Address and Company';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE address DROP CONSTRAINT fk_d4e6f81979b1ad6');
        $this->addSql('ALTER TABLE address DROP CONSTRAINT fk_d4e6f819395c3f3');
        $this->addSql('DROP INDEX uniq_d4e6f819395c3f3');
        $this->addSql('DROP INDEX uniq_d4e6f81979b1ad6');
        $this->addSql('ALTER TABLE address DROP company_id');
        $this->addSql('ALTER TABLE address DROP customer_id');
        $this->addSql('ALTER TABLE company ADD address_id INT NOT NULL');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094FF5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4FBF094FF5B7AF75 ON company (address_id)');
        $this->addSql('ALTER TABLE customer ADD address_id INT NOT NULL');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_81398E09F5B7AF75 ON customer (address_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE customer DROP CONSTRAINT FK_81398E09F5B7AF75');
        $this->addSql('DROP INDEX UNIQ_81398E09F5B7AF75');
        $this->addSql('ALTER TABLE customer DROP address_id');
        $this->addSql('ALTER TABLE address ADD company_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE address ADD customer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT fk_d4e6f81979b1ad6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT fk_d4e6f819395c3f3 FOREIGN KEY (customer_id) REFERENCES customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_d4e6f819395c3f3 ON address (customer_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_d4e6f81979b1ad6 ON address (company_id)');
        $this->addSql('ALTER TABLE company DROP CONSTRAINT FK_4FBF094FF5B7AF75');
        $this->addSql('DROP INDEX UNIQ_4FBF094FF5B7AF75');
        $this->addSql('ALTER TABLE company DROP address_id');
    }
}
