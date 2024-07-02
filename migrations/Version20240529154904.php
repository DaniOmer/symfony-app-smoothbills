<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240529154904 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new entity named UserTheme for ManyToMany associations between User and Theme.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE user_theme_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_theme (id INT NOT NULL, owner_id INT NOT NULL, theme_id INT NOT NULL, is_active BOOLEAN DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75B71C507E3C61F9 ON user_theme (owner_id)');
        $this->addSql('CREATE INDEX IDX_75B71C5059027487 ON user_theme (theme_id)');
        $this->addSql('ALTER TABLE user_theme ADD CONSTRAINT FK_75B71C507E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_theme ADD CONSTRAINT FK_75B71C5059027487 FOREIGN KEY (theme_id) REFERENCES theme (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE theme ADD is_default BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE theme DROP is_active');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT fk_8d93d649d60322ac');
        $this->addSql('DROP INDEX idx_8d93d649d60322ac');
        $this->addSql('ALTER TABLE "user" ADD email VARCHAR(180) NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD roles JSON NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD is_email_validated BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE "user" DROP role_id');
        $this->addSql('ALTER TABLE "user" DROP mail');
        $this->addSql('ALTER TABLE "user" DROP is_mail_validated');
        $this->addSql('ALTER TABLE "user" ALTER owner_id DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER first_name TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE "user" ALTER last_name TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE "user" ALTER password TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE "user" ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE user_theme_id_seq CASCADE');
        $this->addSql('ALTER TABLE user_theme DROP CONSTRAINT FK_75B71C507E3C61F9');
        $this->addSql('ALTER TABLE user_theme DROP CONSTRAINT FK_75B71C5059027487');
        $this->addSql('DROP TABLE user_theme');
        $this->addSql('DROP INDEX UNIQ_IDENTIFIER_EMAIL');
        $this->addSql('ALTER TABLE "user" ADD role_id INT NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD mail VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD is_mail_validated BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" DROP email');
        $this->addSql('ALTER TABLE "user" DROP roles');
        $this->addSql('ALTER TABLE "user" DROP is_email_validated');
        $this->addSql('ALTER TABLE "user" ALTER owner_id SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER password TYPE VARCHAR(128)');
        $this->addSql('ALTER TABLE "user" ALTER first_name TYPE VARCHAR(60)');
        $this->addSql('ALTER TABLE "user" ALTER last_name TYPE VARCHAR(100)');
        $this->addSql('ALTER TABLE "user" ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT fk_8d93d649d60322ac FOREIGN KEY (role_id) REFERENCES role (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_8d93d649d60322ac ON "user" (role_id)');
        $this->addSql('ALTER TABLE theme ADD is_active BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE theme DROP is_default');
    }
}
