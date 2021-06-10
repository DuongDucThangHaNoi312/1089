<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200404141510 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("INSERT INTO cmsblock(name, slug, content) VALUES('Trial Expires In 14 Days Email Html', 'trial-expires-in-14-days-email-html', '')");
        $this->addSql("INSERT INTO cmsblock(name, slug, content) VALUES('Trial Expires In 14 Days Email Text', 'trial-expires-in-14-days-email-text', '')");

        $this->addSql("INSERT INTO cmsblock(name, slug, content) VALUES('Trial Expires In 7 Days Email Html', 'trial-expires-in-7-days-email-html', '')");
        $this->addSql("INSERT INTO cmsblock(name, slug, content) VALUES('Trial Expires In 7 Days Email Text', 'trial-expires-in-7-days-email-text', '')");

        $this->addSql("INSERT INTO cmsblock(name, slug, content) VALUES('Trial Expires Today Email Html', 'trial-expires-today-email-html', '')");
        $this->addSql("INSERT INTO cmsblock(name, slug, content) VALUES('Trial Expires Today Email Text', 'trial-expires-today-email-text', '')");

        $this->addSql("INSERT INTO cmsblock(name, slug, content) VALUES('Trial Expired 7 Days Ago Email Html', 'trial-expired-7-days-ago-email-html', '')");
        $this->addSql("INSERT INTO cmsblock(name, slug, content) VALUES('Trial Expired 7 Days Ago Email Text', 'trial-expired-7-days-ago-email-text', '')");

        $this->addSql("INSERT INTO cmsblock(name, slug, content) VALUES('Trial Expired 14 Days Ago Email Html', 'trial-expired-14-days-ago-email-html', '')");
        $this->addSql("INSERT INTO cmsblock(name, slug, content) VALUES('Trial Expired 14 Days Ago Email Text', 'trial-expired-14-days-ago-email-text', '')");

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("DELETE FROM cmsblock WHERE slug = 'trial-expires-in-14-days-email-html'");
        $this->addSql("DELETE FROM cmsblock WHERE slug = 'trial-expires-in-14-days-email-text'");

        $this->addSql("DELETE FROM cmsblock WHERE slug = 'trial-expires-in-7-days-email-html'");
        $this->addSql("DELETE FROM cmsblock WHERE slug = 'trial-expires-in-7-days-email-text'");

        $this->addSql("DELETE FROM cmsblock WHERE slug = 'trial-expires-today-email-html'");
        $this->addSql("DELETE FROM cmsblock WHERE slug = 'trial-expires-today-email-text'");

        $this->addSql("DELETE FROM cmsblock WHERE slug = 'trial-expired-7-days-ago-email-html'");
        $this->addSql("DELETE FROM cmsblock WHERE slug = 'trial-expired-7-days-ago-email-text'");

        $this->addSql("DELETE FROM cmsblock WHERE slug = 'trial-expired-14-days-ago-email-html'");
        $this->addSql("DELETE FROM cmsblock WHERE slug = 'trial-expired-14-days-ago-email-text'");
    }
}
