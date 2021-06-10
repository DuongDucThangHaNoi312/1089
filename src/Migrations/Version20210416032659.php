<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210416032659 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE job_announcement_impression (id INT AUTO_INCREMENT NOT NULL, job_announcement_id INT NOT NULL, job_seeker_user_id INT DEFAULT NULL, ip_address VARCHAR(255) DEFAULT NULL, user_agent LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_988F85CD7AA6A578 (job_announcement_id), INDEX IDX_988F85CD5A8B1553 (job_seeker_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE job_announcement_impression ADD CONSTRAINT FK_988F85CD7AA6A578 FOREIGN KEY (job_announcement_id) REFERENCES job_announcement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_announcement_impression ADD CONSTRAINT FK_988F85CD5A8B1553 FOREIGN KEY (job_seeker_user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE job_announcement_impression');
    }
}
