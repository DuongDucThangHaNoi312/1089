<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190527095418 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cmsjob_category ADD detail_image VARCHAR(255) DEFAULT NULL, ADD grid_image VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE cmsjob_category SET grid_image = category_image');
        $this->addSql('ALTER TABLE cmsjob_category DROP category_image');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cmsjob_category ADD category_image VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE cmsjob_category SET category_image = grid_image');
        $this->addSql('ALTER TABLE cmsjob_category DROP grid_image, DROP detail_image');
    }
}
