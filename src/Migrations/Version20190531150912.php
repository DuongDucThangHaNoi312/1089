<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190531150912 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE alerted_job_announcement (id INT AUTO_INCREMENT NOT NULL, job_announcement_id INT NOT NULL, job_seeker_id INT NOT NULL, INDEX IDX_22CB11227AA6A578 (job_announcement_id), INDEX IDX_22CB1122C2C5BAA3 (job_seeker_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE alerted_job_announcement ADD CONSTRAINT FK_22CB11227AA6A578 FOREIGN KEY (job_announcement_id) REFERENCES job_announcement (id)');
        $this->addSql('ALTER TABLE alerted_job_announcement ADD CONSTRAINT FK_22CB1122C2C5BAA3 FOREIGN KEY (job_seeker_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE alerted_job_announcement');
    }
}
