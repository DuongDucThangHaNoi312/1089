<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190430164529 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job_title_name ADD created_by_city_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE job_title_name ADD CONSTRAINT FK_7F8E346451DA9CAF FOREIGN KEY (created_by_city_id) REFERENCES city (id)');
        $this->addSql('CREATE INDEX IDX_7F8E346451DA9CAF ON job_title_name (created_by_city_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job_title_name DROP FOREIGN KEY FK_7F8E346451DA9CAF');
        $this->addSql('DROP INDEX IDX_7F8E346451DA9CAF ON job_title_name');
        $this->addSql('ALTER TABLE job_title_name DROP created_by_city_id');
    }
}
