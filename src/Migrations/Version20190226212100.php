<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190226212100 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE dismissed_job_announcement (id INT AUTO_INCREMENT NOT NULL, job_seeker_user_id INT NOT NULL, job_announcement_id INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_6BA13B0B5A8B1553 (job_seeker_user_id), INDEX IDX_6BA13B0B7AA6A578 (job_announcement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dismissed_job_title (id INT AUTO_INCREMENT NOT NULL, job_seeker_user_id INT NOT NULL, job_title_id INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_31FDF2AF5A8B1553 (job_seeker_user_id), INDEX IDX_31FDF2AF6DD822C6 (job_title_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dismissed_job_announcement ADD CONSTRAINT FK_6BA13B0B5A8B1553 FOREIGN KEY (job_seeker_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE dismissed_job_announcement ADD CONSTRAINT FK_6BA13B0B7AA6A578 FOREIGN KEY (job_announcement_id) REFERENCES job_announcement (id)');
        $this->addSql('ALTER TABLE dismissed_job_title ADD CONSTRAINT FK_31FDF2AF5A8B1553 FOREIGN KEY (job_seeker_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE dismissed_job_title ADD CONSTRAINT FK_31FDF2AF6DD822C6 FOREIGN KEY (job_title_id) REFERENCES job_title (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE dismissed_job_announcement');
        $this->addSql('DROP TABLE dismissed_job_title');
    }
}
