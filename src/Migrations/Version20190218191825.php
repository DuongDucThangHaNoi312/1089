<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190218191825 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE subscription DROP INDEX UNIQ_A3C664D35A8B1553, ADD INDEX IDX_A3C664D35A8B1553 (job_seeker_user_id)');
        $this->addSql('ALTER TABLE subscription ADD created_by INT DEFAULT NULL, ADD updated_by INT DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, ADD type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D316FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_A3C664D3DE12AB56 ON subscription (created_by)');
        $this->addSql('CREATE INDEX IDX_A3C664D316FE72E1 ON subscription (updated_by)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE subscription DROP INDEX IDX_A3C664D35A8B1553, ADD UNIQUE INDEX UNIQ_A3C664D35A8B1553 (job_seeker_user_id)');
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D3DE12AB56');
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D316FE72E1');
        $this->addSql('DROP INDEX IDX_A3C664D3DE12AB56 ON subscription');
        $this->addSql('DROP INDEX IDX_A3C664D316FE72E1 ON subscription');
        $this->addSql('ALTER TABLE subscription DROP created_by, DROP updated_by, DROP created_at, DROP updated_at, DROP type');
    }
}
