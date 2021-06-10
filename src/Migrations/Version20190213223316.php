<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190213223316 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE resume ADD city_id INT DEFAULT NULL, ADD state_id INT DEFAULT NULL, DROP city, DROP state');
        $this->addSql('ALTER TABLE resume ADD CONSTRAINT FK_60C1D0A08BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE resume ADD CONSTRAINT FK_60C1D0A05D83CC1 FOREIGN KEY (state_id) REFERENCES state (id)');
        $this->addSql('CREATE INDEX IDX_60C1D0A08BAC62AF ON resume (city_id)');
        $this->addSql('CREATE INDEX IDX_60C1D0A05D83CC1 ON resume (state_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE job_seeker_user_job_level (job_seeker_user_id INT NOT NULL, job_level_id INT NOT NULL, INDEX IDX_67122D4A5A8B1553 (job_seeker_user_id), INDEX IDX_67122D4A38F6EEDC (job_level_id), PRIMARY KEY(job_seeker_user_id, job_level_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE job_seeker_user_job_type (job_seeker_user_id INT NOT NULL, job_type_id INT NOT NULL, INDEX IDX_16C674335A8B1553 (job_seeker_user_id), INDEX IDX_16C674335FA33B08 (job_type_id), PRIMARY KEY(job_seeker_user_id, job_type_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE job_seeker_user_job_level ADD CONSTRAINT FK_67122D4A38F6EEDC FOREIGN KEY (job_level_id) REFERENCES job_level (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_seeker_user_job_level ADD CONSTRAINT FK_67122D4A5A8B1553 FOREIGN KEY (job_seeker_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_seeker_user_job_type ADD CONSTRAINT FK_16C674335A8B1553 FOREIGN KEY (job_seeker_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_seeker_user_job_type ADD CONSTRAINT FK_16C674335FA33B08 FOREIGN KEY (job_type_id) REFERENCES job_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resume DROP FOREIGN KEY FK_60C1D0A08BAC62AF');
        $this->addSql('ALTER TABLE resume DROP FOREIGN KEY FK_60C1D0A05D83CC1');
        $this->addSql('DROP INDEX IDX_60C1D0A08BAC62AF ON resume');
        $this->addSql('DROP INDEX IDX_60C1D0A05D83CC1 ON resume');
        $this->addSql('ALTER TABLE resume ADD city VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD state VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, DROP city_id, DROP state_id');
    }
}
