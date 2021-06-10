<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190224230244 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE job_title_job_category (job_title_id INT NOT NULL, job_category_id INT NOT NULL, INDEX IDX_4A9D35CF6DD822C6 (job_title_id), INDEX IDX_4A9D35CF712A86AB (job_category_id), PRIMARY KEY(job_title_id, job_category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE job_title_job_category ADD CONSTRAINT FK_4A9D35CF6DD822C6 FOREIGN KEY (job_title_id) REFERENCES job_title (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_title_job_category ADD CONSTRAINT FK_4A9D35CF712A86AB FOREIGN KEY (job_category_id) REFERENCES job_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_title DROP FOREIGN KEY FK_2A6AC51B12469DE2');
        $this->addSql('DROP INDEX IDX_2A6AC51B12469DE2 ON job_title');
        $this->addSql('ALTER TABLE job_title DROP category_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE job_title_job_category');
        $this->addSql('ALTER TABLE job_title ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE job_title ADD CONSTRAINT FK_2A6AC51B12469DE2 FOREIGN KEY (category_id) REFERENCES job_category (id)');
        $this->addSql('CREATE INDEX IDX_2A6AC51B12469DE2 ON job_title (category_id)');
    }
}
