<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191218175734 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE subscription_change_request DROP FOREIGN KEY FK_E19103F29B8CE200');
        $this->addSql('DROP INDEX IDX_E19103F29B8CE200 ON subscription_change_request');
        $this->addSql('ALTER TABLE subscription_change_request ADD old_subscription_plan_id INT NOT NULL, CHANGE subscription_plan_id new_subscription_plan_id INT NOT NULL');
        $this->addSql('ALTER TABLE subscription_change_request ADD CONSTRAINT FK_E19103F26C4D9255 FOREIGN KEY (new_subscription_plan_id) REFERENCES subscription_plan (id)');
        $this->addSql('UPDATE subscription_change_request LEFT JOIN subscription ON subscription.id = subscription_id SET old_subscription_plan_id = subscription.subscription_plan_id WHERE old_subscription_plan_id = 0');
        $this->addSql('ALTER TABLE subscription_change_request ADD CONSTRAINT FK_E19103F2E62DB981 FOREIGN KEY (old_subscription_plan_id) REFERENCES subscription_plan (id)');
        $this->addSql('CREATE INDEX IDX_E19103F26C4D9255 ON subscription_change_request (new_subscription_plan_id)');
        $this->addSql('CREATE INDEX IDX_E19103F2E62DB981 ON subscription_change_request (old_subscription_plan_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE subscription_change_request DROP FOREIGN KEY FK_E19103F26C4D9255');
        $this->addSql('ALTER TABLE subscription_change_request DROP FOREIGN KEY FK_E19103F2E62DB981');
        $this->addSql('DROP INDEX IDX_E19103F26C4D9255 ON subscription_change_request');
        $this->addSql('DROP INDEX IDX_E19103F2E62DB981 ON subscription_change_request');
        $this->addSql('ALTER TABLE subscription_change_request ADD subscription_plan_id INT NOT NULL, DROP new_subscription_plan_id, DROP old_subscription_plan_id');
        $this->addSql('ALTER TABLE subscription_change_request ADD CONSTRAINT FK_E19103F29B8CE200 FOREIGN KEY (subscription_plan_id) REFERENCES subscription_plan (id)');
        $this->addSql('CREATE INDEX IDX_E19103F29B8CE200 ON subscription_change_request (subscription_plan_id)');
    }
}
