<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190204195456 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE resume_job_category (resume_id INT NOT NULL, job_category_id INT NOT NULL, INDEX IDX_C53872DDD262AF09 (resume_id), INDEX IDX_C53872DD712A86AB (job_category_id), PRIMARY KEY(resume_id, job_category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE resume_job_title (resume_id INT NOT NULL, job_title_id INT NOT NULL, INDEX IDX_E71324A2D262AF09 (resume_id), INDEX IDX_E71324A26DD822C6 (job_title_id), PRIMARY KEY(resume_id, job_title_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE resume_county (resume_id INT NOT NULL, county_id INT NOT NULL, INDEX IDX_1B17E18FD262AF09 (resume_id), INDEX IDX_1B17E18F85E73F45 (county_id), PRIMARY KEY(resume_id, county_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE resume_job_category ADD CONSTRAINT FK_C53872DDD262AF09 FOREIGN KEY (resume_id) REFERENCES resume (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resume_job_category ADD CONSTRAINT FK_C53872DD712A86AB FOREIGN KEY (job_category_id) REFERENCES job_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resume_job_title ADD CONSTRAINT FK_E71324A2D262AF09 FOREIGN KEY (resume_id) REFERENCES resume (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resume_job_title ADD CONSTRAINT FK_E71324A26DD822C6 FOREIGN KEY (job_title_id) REFERENCES job_title (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resume_county ADD CONSTRAINT FK_1B17E18FD262AF09 FOREIGN KEY (resume_id) REFERENCES resume (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resume_county ADD CONSTRAINT FK_1B17E18F85E73F45 FOREIGN KEY (county_id) REFERENCES county (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resume ADD interested_job_type_id INT DEFAULT NULL, ADD interested_job_level_id INT DEFAULT NULL, ADD city VARCHAR(255) DEFAULT NULL, ADD state VARCHAR(255) NOT NULL, ADD current_job_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE resume ADD CONSTRAINT FK_60C1D0A0ADDA6322 FOREIGN KEY (interested_job_type_id) REFERENCES job_type (id)');
        $this->addSql('ALTER TABLE resume ADD CONSTRAINT FK_60C1D0A0E3BF5E52 FOREIGN KEY (interested_job_level_id) REFERENCES job_level (id)');
        $this->addSql('CREATE INDEX IDX_60C1D0A0ADDA6322 ON resume (interested_job_type_id)');
        $this->addSql('CREATE INDEX IDX_60C1D0A0E3BF5E52 ON resume (interested_job_level_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE resume_job_category');
        $this->addSql('DROP TABLE resume_job_title');
        $this->addSql('DROP TABLE resume_county');
        $this->addSql('ALTER TABLE resume DROP FOREIGN KEY FK_60C1D0A0ADDA6322');
        $this->addSql('ALTER TABLE resume DROP FOREIGN KEY FK_60C1D0A0E3BF5E52');
        $this->addSql('DROP INDEX IDX_60C1D0A0ADDA6322 ON resume');
        $this->addSql('DROP INDEX IDX_60C1D0A0E3BF5E52 ON resume');
        $this->addSql('ALTER TABLE resume DROP interested_job_type_id, DROP interested_job_level_id, DROP city, DROP state, DROP current_job_title');
    }
}
