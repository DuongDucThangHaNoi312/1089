<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190227185425 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD state_id INT DEFAULT NULL, ADD county_id INT DEFAULT NULL');
        $this->addSql('UPDATE user INNER JOIN city ON city.name = city SET user.city_id = city.id');
        $this->addSql('UPDATE user INNER JOIN state ON state.name = user.state SET user.state_id = state.id');
        $this->addSql('UPDATE user INNER JOIN county ON county.name = user.county SET user.county_id = county.id;');
        $this->addSql('ALTER TABLE user DROP city, DROP state, DROP county');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6495D83CC1 FOREIGN KEY (state_id) REFERENCES state (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64985E73F45 FOREIGN KEY (county_id) REFERENCES county (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6495D83CC1 ON user (state_id)');
        $this->addSql('CREATE INDEX IDX_8D93D64985E73F45 ON user (county_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6495D83CC1');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64985E73F45');
        $this->addSql('DROP INDEX IDX_8D93D6495D83CC1 ON user');
        $this->addSql('DROP INDEX IDX_8D93D64985E73F45 ON user');
        $this->addSql('ALTER TABLE user ADD city VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD state VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD county VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('UPDATE user INNER JOIN city ON city.id = user.city_id SET user.city = city.name');
        $this->addSql('UPDATE user INNER JOIN county ON city.id = user.county_id SET user.county = city.name');
        $this->addSql('UPDATE user INNER JOIN state ON state.id = user.state_id SET user.state = state.name');
        $this->addSql('ALTER TABLE user DROP state_id, DROP county_id');
    }
}
