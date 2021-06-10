<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200318124818 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("UPDATE user SET receive_alerts_for_submitted_interest = 1 WHERE receive_alerts_for_submitted_interest is NULL");
        $this->addSql("UPDATE user SET receive_alerts_for_jobs_matching_saved_search_criteria = 1 WHERE receive_alerts_for_jobs_matching_saved_search_criteria is NULL");
        $this->addSql("UPDATE user SET notification_preference_for_submitted_interest = 'daily' WHERE notification_preference_for_submitted_interest is NULL");
        $this->addSql("UPDATE user SET notification_preference_for_jobs_matching_saved_search_criteria = 'daily' WHERE notification_preference_for_jobs_matching_saved_search_criteria is NULL");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

    }
}
