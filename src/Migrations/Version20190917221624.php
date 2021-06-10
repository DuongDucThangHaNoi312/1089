<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190917221624 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE subscription_change_request ADD subscription_plan_id INT NOT NULL');
        $this->addSql('ALTER TABLE subscription_change_request ADD CONSTRAINT FK_E19103F29B8CE200 FOREIGN KEY (subscription_plan_id) REFERENCES subscription_plan (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E19103F29B8CE200 ON subscription_change_request (subscription_plan_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE subscription_change_request DROP FOREIGN KEY FK_E19103F29B8CE200');
        $this->addSql('DROP INDEX UNIQ_E19103F29B8CE200 ON subscription_change_request');
        $this->addSql('ALTER TABLE subscription_change_request DROP subscription_plan_id');
    }
}
