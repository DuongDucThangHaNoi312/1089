<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190515075712 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job_announcement ADD city_id INT DEFAULT NULL, ADD state_id INT DEFAULT NULL, ADD street VARCHAR(255) DEFAULT NULL, ADD zipcode VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE job_announcement ADD CONSTRAINT FK_42DA4C338BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE job_announcement ADD CONSTRAINT FK_42DA4C335D83CC1 FOREIGN KEY (state_id) REFERENCES state (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_42DA4C338BAC62AF ON job_announcement (city_id)');
        $this->addSql('CREATE INDEX IDX_42DA4C335D83CC1 ON job_announcement (state_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job_announcement DROP FOREIGN KEY FK_42DA4C338BAC62AF');
        $this->addSql('ALTER TABLE job_announcement DROP FOREIGN KEY FK_42DA4C335D83CC1');
        $this->addSql('DROP INDEX IDX_42DA4C338BAC62AF ON job_announcement');
        $this->addSql('DROP INDEX IDX_42DA4C335D83CC1 ON job_announcement');
        $this->addSql('ALTER TABLE job_announcement DROP city_id, DROP state_id, DROP street, DROP zipcode');
    }
}
