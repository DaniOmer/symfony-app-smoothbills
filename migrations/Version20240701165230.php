<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240701165230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // // this up() migration is auto-generated, please modify it to your needs
        // $this->addSql('ALTER TABLE one_time_payment DROP CONSTRAINT fk_90a31c7a4c3a3bb');
        // $this->addSql('DROP INDEX idx_90a31c7a4c3a3bb');
        // $this->addSql('ALTER TABLE one_time_payment DROP payment_id');
        // $this->addSql('ALTER TABLE one_time_payment ALTER stripe_invoice_id DROP NOT NULL');
        // $this->addSql('ALTER TABLE payment ADD one_time_payment_id INT DEFAULT NULL');
        // $this->addSql('ALTER TABLE payment ADD recurring_payment_id INT DEFAULT NULL');
        // $this->addSql('ALTER TABLE payment ADD uid UUID NOT NULL');
        // $this->addSql('ALTER TABLE payment ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        // $this->addSql('ALTER TABLE payment ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        // $this->addSql('ALTER TABLE payment ALTER stripe_payment_method DROP NOT NULL');
        // $this->addSql('ALTER TABLE payment ALTER stripe_last_digits DROP NOT NULL');
        // $this->addSql('COMMENT ON COLUMN payment.uid IS \'(DC2Type:uuid)\'');
        // $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D968F8729 FOREIGN KEY (one_time_payment_id) REFERENCES one_time_payment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        // $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DB2CFAA1A FOREIGN KEY (recurring_payment_id) REFERENCES recurring_payment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        // $this->addSql('CREATE UNIQUE INDEX UNIQ_6D28840D539B0606 ON payment (uid)');
        // $this->addSql('CREATE UNIQUE INDEX UNIQ_6D28840D968F8729 ON payment (one_time_payment_id)');
        // $this->addSql('CREATE UNIQUE INDEX UNIQ_6D28840DB2CFAA1A ON payment (recurring_payment_id)');
        // $this->addSql('ALTER TABLE recurring_payment DROP CONSTRAINT fk_b5de11844c3a3bb');
        // $this->addSql('DROP INDEX idx_b5de11844c3a3bb');
        // $this->addSql('ALTER TABLE recurring_payment DROP payment_id');
        // $this->addSql('ALTER TABLE recurring_payment ALTER stripe_subscription_id DROP NOT NULL');
        // $this->addSql('ALTER TABLE "user" DROP reset_token');
        // $this->addSql('ALTER TABLE "user" DROP reset_token_expires_at');
    }

    public function down(Schema $schema): void
    {
        // // this down() migration is auto-generated, please modify it to your needs
        // $this->addSql('CREATE SCHEMA public');
        // $this->addSql('ALTER TABLE one_time_payment ADD payment_id INT NOT NULL');
        // $this->addSql('ALTER TABLE one_time_payment ALTER stripe_invoice_id SET NOT NULL');
        // $this->addSql('ALTER TABLE one_time_payment ADD CONSTRAINT fk_90a31c7a4c3a3bb FOREIGN KEY (payment_id) REFERENCES payment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        // $this->addSql('CREATE INDEX idx_90a31c7a4c3a3bb ON one_time_payment (payment_id)');
        // $this->addSql('ALTER TABLE "user" ADD reset_token VARCHAR(255) DEFAULT NULL');
        // $this->addSql('ALTER TABLE "user" ADD reset_token_expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        // $this->addSql('ALTER TABLE payment DROP CONSTRAINT FK_6D28840D968F8729');
        // $this->addSql('ALTER TABLE payment DROP CONSTRAINT FK_6D28840DB2CFAA1A');
        // $this->addSql('DROP INDEX UNIQ_6D28840D539B0606');
        // $this->addSql('DROP INDEX UNIQ_6D28840D968F8729');
        // $this->addSql('DROP INDEX UNIQ_6D28840DB2CFAA1A');
        // $this->addSql('ALTER TABLE payment DROP one_time_payment_id');
        // $this->addSql('ALTER TABLE payment DROP recurring_payment_id');
        // $this->addSql('ALTER TABLE payment DROP uid');
        // $this->addSql('ALTER TABLE payment DROP created_at');
        // $this->addSql('ALTER TABLE payment DROP updated_at');
        // $this->addSql('ALTER TABLE payment ALTER stripe_payment_method SET NOT NULL');
        // $this->addSql('ALTER TABLE payment ALTER stripe_last_digits SET NOT NULL');
        // $this->addSql('ALTER TABLE recurring_payment ADD payment_id INT NOT NULL');
        // $this->addSql('ALTER TABLE recurring_payment ALTER stripe_subscription_id SET NOT NULL');
        // $this->addSql('ALTER TABLE recurring_payment ADD CONSTRAINT fk_b5de11844c3a3bb FOREIGN KEY (payment_id) REFERENCES payment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        // $this->addSql('CREATE INDEX idx_b5de11844c3a3bb ON recurring_payment (payment_id)');
    }
}
