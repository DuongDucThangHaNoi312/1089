<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190414185452 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job_title DROP FOREIGN KEY FK_2A6AC51B98B5FB63');
        $this->addSql('ALTER TABLE job_title ADD CONSTRAINT FK_2A6AC51B98B5FB63 FOREIGN KEY (job_title_name_id) REFERENCES job_title_name (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job_title DROP FOREIGN KEY FK_2A6AC51B98B5FB63');
        $this->addSql('ALTER TABLE job_title ADD CONSTRAINT FK_2A6AC51B98B5FB63 FOREIGN KEY (job_title_name_id) REFERENCES job_title_name (id)');
    }
}
