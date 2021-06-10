<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190325201346 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE subscription ADD stripe_subscription_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3B5DBB761 FOREIGN KEY (stripe_subscription_id) REFERENCES stripe_subscription (id)');
        $this->addSql('CREATE INDEX IDX_A3C664D3B5DBB761 ON subscription (stripe_subscription_id)');
        $this->addSql('ALTER TABLE subscription_plan ADD stripe_plan_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE subscription_plan ADD CONSTRAINT FK_EA664B63C75E3361 FOREIGN KEY (stripe_plan_id) REFERENCES stripe_plan (id)');
        $this->addSql('CREATE INDEX IDX_EA664B63C75E3361 ON subscription_plan (stripe_plan_id)');
        $this->addSql('ALTER TABLE user ADD stripe_customer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649708DC647 FOREIGN KEY (stripe_customer_id) REFERENCES stripe_customer (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649708DC647 ON user (stripe_customer_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D3B5DBB761');
        $this->addSql('DROP INDEX IDX_A3C664D3B5DBB761 ON subscription');
        $this->addSql('ALTER TABLE subscription DROP stripe_subscription_id');
        $this->addSql('ALTER TABLE subscription_plan DROP FOREIGN KEY FK_EA664B63C75E3361');
        $this->addSql('DROP INDEX IDX_EA664B63C75E3361 ON subscription_plan');
        $this->addSql('ALTER TABLE subscription_plan DROP stripe_plan_id');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649708DC647');
        $this->addSql('DROP INDEX IDX_8D93D649708DC647 ON user');
        $this->addSql('ALTER TABLE user DROP stripe_customer_id');
    }
}
