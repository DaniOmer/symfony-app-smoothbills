<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240629223359 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'set stripe_payment_method and stripe_last_digits nullable';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment ALTER stripe_payment_method DROP NOT NULL');
        $this->addSql('ALTER TABLE payment ALTER stripe_last_digits DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment ALTER stripe_payment_method SET NOT NULL');
        $this->addSql('ALTER TABLE payment ALTER stripe_last_digits SET NOT NULL');
    }
}
