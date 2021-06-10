<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190611203402 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job_title ADD marked_vacant_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE job_title ADD CONSTRAINT FK_2A6AC51BF0A830BF FOREIGN KEY (marked_vacant_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_2A6AC51BF0A830BF ON job_title (marked_vacant_by_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job_title DROP FOREIGN KEY FK_2A6AC51BF0A830BF');
        $this->addSql('DROP INDEX IDX_2A6AC51BF0A830BF ON job_title');
        $this->addSql('ALTER TABLE job_title DROP marked_vacant_by_id');
    }
}
