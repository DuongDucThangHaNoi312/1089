<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190406162829 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE resume DROP FOREIGN KEY FK_60C1D0A05D83CC1');
        $this->addSql('ALTER TABLE resume DROP FOREIGN KEY FK_60C1D0A08BAC62AF');
        $this->addSql('ALTER TABLE resume DROP FOREIGN KEY FK_60C1D0A0ADDA6322');
        $this->addSql('ALTER TABLE resume ADD CONSTRAINT FK_60C1D0A05D83CC1 FOREIGN KEY (state_id) REFERENCES state (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE resume ADD CONSTRAINT FK_60C1D0A08BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE resume ADD CONSTRAINT FK_60C1D0A0ADDA6322 FOREIGN KEY (interested_job_type_id) REFERENCES job_type (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE resume DROP FOREIGN KEY FK_60C1D0A08BAC62AF');
        $this->addSql('ALTER TABLE resume DROP FOREIGN KEY FK_60C1D0A05D83CC1');
        $this->addSql('ALTER TABLE resume DROP FOREIGN KEY FK_60C1D0A0ADDA6322');
        $this->addSql('ALTER TABLE resume ADD CONSTRAINT FK_60C1D0A08BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE resume ADD CONSTRAINT FK_60C1D0A05D83CC1 FOREIGN KEY (state_id) REFERENCES state (id)');
        $this->addSql('ALTER TABLE resume ADD CONSTRAINT FK_60C1D0A0ADDA6322 FOREIGN KEY (interested_job_type_id) REFERENCES job_type (id)');
    }
}
