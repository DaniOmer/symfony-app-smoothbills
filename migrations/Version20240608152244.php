<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240608152244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE analytic_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE article_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE company_subscription_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE customer_subscription_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE financial_report_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE graphic_chart_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE invoice_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE invoice_status_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE one_time_payment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE payment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE recurring_payment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE section_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE service_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE service_status_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE subscription_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE analytic (id INT NOT NULL, name VARCHAR(255) NOT NULL, short_description VARCHAR(45) NOT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE analytic_user (analytic_id INT NOT NULL, user_id INT NOT NULL, is_active BOOLEAN NOT NULL, PRIMARY KEY(analytic_id, user_id))');
        $this->addSql('CREATE INDEX IDX_B5339820345D6718 ON analytic_user (analytic_id)');
        $this->addSql('CREATE INDEX IDX_B5339820A76ED395 ON analytic_user (user_id)');
        $this->addSql('CREATE TABLE article (id INT NOT NULL, title VARCHAR(45) NOT NULL, content TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, thumbnail VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE category (id INT NOT NULL, service_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_64C19C1ED5CA9E6 ON category (service_id)');
        $this->addSql('CREATE TABLE company_subscription (id INT NOT NULL, subscription_id INT NOT NULL, company_id INT NOT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, trial_end_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, stripe_status VARCHAR(45) NOT NULL, stripe_payment_method VARCHAR(255) NOT NULL, stripe_last_digits VARCHAR(4) NOT NULL, stripe_subscription_id VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5D0BAE1D9A1887DC ON company_subscription (subscription_id)');
        $this->addSql('CREATE INDEX IDX_5D0BAE1D979B1AD6 ON company_subscription (company_id)');
        $this->addSql('CREATE TABLE customer_subscription (id INT NOT NULL, starting_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expiration_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE financial_report (id INT NOT NULL, user_id INT NOT NULL, invoice_id INT NOT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6F8F2B37A76ED395 ON financial_report (user_id)');
        $this->addSql('CREATE INDEX IDX_6F8F2B372989F1FD ON financial_report (invoice_id)');
        $this->addSql('CREATE TABLE graphic_chart (id INT NOT NULL, company_id INT NOT NULL, company_logo VARCHAR(255) DEFAULT NULL, background_color VARCHAR(255) DEFAULT NULL, title_color VARCHAR(255) DEFAULT NULL, title_font VARCHAR(255) DEFAULT NULL, content_font VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9F7BE0CE979B1AD6 ON graphic_chart (company_id)');
        $this->addSql('CREATE TABLE invoice (id INT NOT NULL, invoice_status_id INT NOT NULL, company_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, uuid VARCHAR(45) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_90651744D17F50A6 ON invoice (uuid)');
        $this->addSql('CREATE INDEX IDX_90651744E58F121 ON invoice (invoice_status_id)');
        $this->addSql('CREATE INDEX IDX_90651744979B1AD6 ON invoice (company_id)');
        $this->addSql('CREATE TABLE invoice_status (id INT NOT NULL, name VARCHAR(45) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE one_time_payment (id INT NOT NULL, payment_id INT NOT NULL, status VARCHAR(100) NOT NULL, payment_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, stripe_invoice_id VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_90A31C7A4C3A3BB ON one_time_payment (payment_id)');
        $this->addSql('CREATE TABLE payment (id INT NOT NULL, invoice_id INT NOT NULL, amount NUMERIC(10, 2) NOT NULL, stripe_payment_method VARCHAR(255) NOT NULL, stripe_last_digits VARCHAR(4) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6D28840D2989F1FD ON payment (invoice_id)');
        $this->addSql('CREATE TABLE recurring_payment (id INT NOT NULL, payment_id INT NOT NULL, status VARCHAR(100) NOT NULL, payment_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, stripe_subscription_id VARCHAR(255) NOT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B5DE11844C3A3BB ON recurring_payment (payment_id)');
        $this->addSql('CREATE TABLE section (id INT NOT NULL, name VARCHAR(255) NOT NULL, link VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE service (id INT NOT NULL, company_id INT NOT NULL, service_status_id INT NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, price NUMERIC(10, 2) NOT NULL, estimated_duration VARCHAR(45) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E19D9AD2979B1AD6 ON service (company_id)');
        $this->addSql('CREATE INDEX IDX_E19D9AD233663AF7 ON service (service_status_id)');
        $this->addSql('CREATE TABLE service_status (id INT NOT NULL, name VARCHAR(45) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE subscription (id INT NOT NULL, name VARCHAR(255) NOT NULL, price NUMERIC(10, 2) NOT NULL, duration INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE analytic_user ADD CONSTRAINT FK_B5339820345D6718 FOREIGN KEY (analytic_id) REFERENCES analytic (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE analytic_user ADD CONSTRAINT FK_B5339820A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE company_subscription ADD CONSTRAINT FK_5D0BAE1D9A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE company_subscription ADD CONSTRAINT FK_5D0BAE1D979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE financial_report ADD CONSTRAINT FK_6F8F2B37A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE financial_report ADD CONSTRAINT FK_6F8F2B372989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE graphic_chart ADD CONSTRAINT FK_9F7BE0CE979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744E58F121 FOREIGN KEY (invoice_status_id) REFERENCES invoice_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE one_time_payment ADD CONSTRAINT FK_90A31C7A4C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D2989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE recurring_payment ADD CONSTRAINT FK_B5DE11844C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD2979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD233663AF7 FOREIGN KEY (service_status_id) REFERENCES service_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE analytic_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE article_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE category_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE company_subscription_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE customer_subscription_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE financial_report_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE graphic_chart_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE invoice_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE invoice_status_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE one_time_payment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE payment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE recurring_payment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE section_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE service_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE service_status_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE subscription_id_seq CASCADE');
        $this->addSql('ALTER TABLE analytic_user DROP CONSTRAINT FK_B5339820345D6718');
        $this->addSql('ALTER TABLE analytic_user DROP CONSTRAINT FK_B5339820A76ED395');
        $this->addSql('ALTER TABLE category DROP CONSTRAINT FK_64C19C1ED5CA9E6');
        $this->addSql('ALTER TABLE company_subscription DROP CONSTRAINT FK_5D0BAE1D9A1887DC');
        $this->addSql('ALTER TABLE company_subscription DROP CONSTRAINT FK_5D0BAE1D979B1AD6');
        $this->addSql('ALTER TABLE financial_report DROP CONSTRAINT FK_6F8F2B37A76ED395');
        $this->addSql('ALTER TABLE financial_report DROP CONSTRAINT FK_6F8F2B372989F1FD');
        $this->addSql('ALTER TABLE graphic_chart DROP CONSTRAINT FK_9F7BE0CE979B1AD6');
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT FK_90651744E58F121');
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT FK_90651744979B1AD6');
        $this->addSql('ALTER TABLE one_time_payment DROP CONSTRAINT FK_90A31C7A4C3A3BB');
        $this->addSql('ALTER TABLE payment DROP CONSTRAINT FK_6D28840D2989F1FD');
        $this->addSql('ALTER TABLE recurring_payment DROP CONSTRAINT FK_B5DE11844C3A3BB');
        $this->addSql('ALTER TABLE service DROP CONSTRAINT FK_E19D9AD2979B1AD6');
        $this->addSql('ALTER TABLE service DROP CONSTRAINT FK_E19D9AD233663AF7');
        $this->addSql('DROP TABLE analytic');
        $this->addSql('DROP TABLE analytic_user');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE company_subscription');
        $this->addSql('DROP TABLE customer_subscription');
        $this->addSql('DROP TABLE financial_report');
        $this->addSql('DROP TABLE graphic_chart');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('DROP TABLE invoice_status');
        $this->addSql('DROP TABLE one_time_payment');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE recurring_payment');
        $this->addSql('DROP TABLE section');
        $this->addSql('DROP TABLE service');
        $this->addSql('DROP TABLE service_status');
        $this->addSql('DROP TABLE subscription');
    }
}
