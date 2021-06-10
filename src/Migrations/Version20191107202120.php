<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191107202120 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE resume_job_level (resume_id INT NOT NULL, job_level_id INT NOT NULL, INDEX IDX_56CF90DAD262AF09 (resume_id), INDEX IDX_56CF90DA38F6EEDC (job_level_id), PRIMARY KEY(resume_id, job_level_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE resume_job_level ADD CONSTRAINT FK_56CF90DAD262AF09 FOREIGN KEY (resume_id) REFERENCES resume (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resume_job_level ADD CONSTRAINT FK_56CF90DA38F6EEDC FOREIGN KEY (job_level_id) REFERENCES job_level (id) ON DELETE CASCADE');
        $this->addSql('INSERT INTO resume_job_level (resume_id, job_level_id) SELECT id, interested_job_level_id FROM resume WHERE interested_job_level_id IS NOT NULL');
        $this->addSql('ALTER TABLE resume DROP FOREIGN KEY FK_60C1D0A0E3BF5E52');
        $this->addSql('DROP INDEX IDX_60C1D0A0E3BF5E52 ON resume');
        $this->addSql('ALTER TABLE resume DROP interested_job_level_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE resume_job_level');
        $this->addSql('ALTER TABLE resume ADD interested_job_level_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE resume ADD CONSTRAINT FK_60C1D0A0E3BF5E52 FOREIGN KEY (interested_job_level_id) REFERENCES job_level (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_60C1D0A0E3BF5E52 ON resume (interested_job_level_id)');
    }
}
