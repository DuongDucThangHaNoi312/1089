<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190414211322 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE job_seeker_user_job_title_name (job_seeker_user_id INT NOT NULL, job_title_name_id INT NOT NULL, INDEX IDX_54FC92065A8B1553 (job_seeker_user_id), INDEX IDX_54FC920698B5FB63 (job_title_name_id), PRIMARY KEY(job_seeker_user_id, job_title_name_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE job_seeker_user_job_title_name ADD CONSTRAINT FK_54FC92065A8B1553 FOREIGN KEY (job_seeker_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_seeker_user_job_title_name ADD CONSTRAINT FK_54FC920698B5FB63 FOREIGN KEY (job_title_name_id) REFERENCES job_title_name (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE job_seeker_user_job_title');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE job_seeker_user_job_title (job_seeker_user_id INT NOT NULL, job_title_id INT NOT NULL, INDEX IDX_D6CE99325A8B1553 (job_seeker_user_id), INDEX IDX_D6CE99326DD822C6 (job_title_id), PRIMARY KEY(job_seeker_user_id, job_title_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE job_seeker_user_job_title ADD CONSTRAINT FK_D6CE99325A8B1553 FOREIGN KEY (job_seeker_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_seeker_user_job_title ADD CONSTRAINT FK_D6CE99326DD822C6 FOREIGN KEY (job_title_id) REFERENCES job_title (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE job_seeker_user_job_title_name');
    }
}
