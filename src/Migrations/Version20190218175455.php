<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190218175455 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE job_seeker_subscription_plan_job_level (job_seeker_subscription_plan_id INT NOT NULL, job_level_id INT NOT NULL, INDEX IDX_F05A09892AC7FC5D (job_seeker_subscription_plan_id), INDEX IDX_F05A098938F6EEDC (job_level_id), PRIMARY KEY(job_seeker_subscription_plan_id, job_level_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subscription (id INT AUTO_INCREMENT NOT NULL, job_seeker_user_id INT NOT NULL, subscription_plan_id INT NOT NULL, expires_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_A3C664D35A8B1553 (job_seeker_user_id), INDEX IDX_A3C664D39B8CE200 (subscription_plan_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE job_seeker_subscription_plan_job_level ADD CONSTRAINT FK_F05A09892AC7FC5D FOREIGN KEY (job_seeker_subscription_plan_id) REFERENCES subscription_plan (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_seeker_subscription_plan_job_level ADD CONSTRAINT FK_F05A098938F6EEDC FOREIGN KEY (job_level_id) REFERENCES job_level (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D35A8B1553 FOREIGN KEY (job_seeker_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D39B8CE200 FOREIGN KEY (subscription_plan_id) REFERENCES subscription_plan (id)');
        $this->addSql('ALTER TABLE subscription_plan ADD count_saved_job_searches INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE job_seeker_subscription_plan_job_level');
        $this->addSql('DROP TABLE subscription');
        $this->addSql('ALTER TABLE subscription_plan DROP count_saved_job_searches');
    }
}
