<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190710072627 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job_announcement ADD job_title_city_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE job_announcement ADD CONSTRAINT FK_42DA4C33620E051A FOREIGN KEY (job_title_city_id) REFERENCES city (id)');
        $this->addSql('CREATE INDEX IDX_42DA4C33620E051A ON job_announcement (job_title_city_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job_announcement DROP FOREIGN KEY FK_42DA4C33620E051A');
        $this->addSql('DROP INDEX IDX_42DA4C33620E051A ON job_announcement');
        $this->addSql('ALTER TABLE job_announcement DROP job_title_city_id');
    }
}
