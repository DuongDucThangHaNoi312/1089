<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190414183612 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE job_title_name (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE job_title ADD job_title_name_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE job_title ADD CONSTRAINT FK_2A6AC51B98B5FB63 FOREIGN KEY (job_title_name_id) REFERENCES job_title_name (id)');
        $this->addSql('CREATE INDEX IDX_2A6AC51B98B5FB63 ON job_title (job_title_name_id)');
        $this->addSql('ALTER TABLE job_title_name ADD created_by INT DEFAULT NULL, ADD updated_by INT DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE job_title_name ADD CONSTRAINT FK_7F8E3464DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE job_title_name ADD CONSTRAINT FK_7F8E346416FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_7F8E3464DE12AB56 ON job_title_name (created_by)');
        $this->addSql('CREATE INDEX IDX_7F8E346416FE72E1 ON job_title_name (updated_by)');

        // GLR Custom
        $this->addSql('insert into job_title_name (name, created_at, updated_at) select name, now(), now() from job_title group by name');
        $this->addSql('update job_title, job_title_name set job_title.job_title_name_id = job_title_name.id where job_title_name.name = job_title.name');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job_title_name DROP FOREIGN KEY FK_7F8E3464DE12AB56');
        $this->addSql('ALTER TABLE job_title_name DROP FOREIGN KEY FK_7F8E346416FE72E1');
        $this->addSql('DROP INDEX IDX_7F8E3464DE12AB56 ON job_title_name');
        $this->addSql('DROP INDEX IDX_7F8E346416FE72E1 ON job_title_name');
        $this->addSql('ALTER TABLE job_title_name DROP created_by, DROP updated_by, DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE job_title DROP FOREIGN KEY FK_2A6AC51B98B5FB63');
        $this->addSql('DROP TABLE job_title_name');
        $this->addSql('DROP INDEX IDX_2A6AC51B98B5FB63 ON job_title');
        $this->addSql('ALTER TABLE job_title DROP job_title_name_id');
    }
}
