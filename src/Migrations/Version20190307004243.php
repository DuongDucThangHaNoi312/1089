<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190307004243 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job_title DROP FOREIGN KEY FK_2A6AC51B41859289');
        $this->addSql('ALTER TABLE job_title ADD CONSTRAINT FK_2A6AC51B41859289 FOREIGN KEY (division_id) REFERENCES division (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job_title DROP FOREIGN KEY FK_2A6AC51B41859289');
        $this->addSql('ALTER TABLE job_title ADD CONSTRAINT FK_2A6AC51B41859289 FOREIGN KEY (division_id) REFERENCES division (id)');
    }
}
