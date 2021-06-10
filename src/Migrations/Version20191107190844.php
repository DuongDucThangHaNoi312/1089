<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191107190844 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE job_seeker_user_job_level (job_seeker_user_id INT NOT NULL, job_level_id INT NOT NULL, INDEX IDX_67122D4A5A8B1553 (job_seeker_user_id), INDEX IDX_67122D4A38F6EEDC (job_level_id), PRIMARY KEY(job_seeker_user_id, job_level_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE job_seeker_user_job_level ADD CONSTRAINT FK_67122D4A5A8B1553 FOREIGN KEY (job_seeker_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_seeker_user_job_level ADD CONSTRAINT FK_67122D4A38F6EEDC FOREIGN KEY (job_level_id) REFERENCES job_level (id) ON DELETE CASCADE');
        $this->addSql('INSERT INTO job_seeker_user_job_level (job_seeker_user_id, job_level_id) SELECT id, interested_job_level_id FROM user WHERE interested_job_level_id IS NOT NULL');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649E3BF5E52');
        $this->addSql('DROP INDEX IDX_8D93D649E3BF5E52 ON user');
        $this->addSql('ALTER TABLE user DROP interested_job_level_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE job_seeker_user_job_level');
        $this->addSql('ALTER TABLE user ADD interested_job_level_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649E3BF5E52 FOREIGN KEY (interested_job_level_id) REFERENCES job_level (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_8D93D649E3BF5E52 ON user (interested_job_level_id)');
    }
}
