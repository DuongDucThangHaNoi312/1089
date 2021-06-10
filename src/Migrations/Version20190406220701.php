<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190406220701 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE city DROP FOREIGN KEY FK_2D5B023416FE72E1');
        $this->addSql('ALTER TABLE city DROP FOREIGN KEY FK_2D5B0234DE12AB56');
        $this->addSql('ALTER TABLE city ADD CONSTRAINT FK_2D5B023416FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE city ADD CONSTRAINT FK_2D5B0234DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE job_announcement DROP FOREIGN KEY FK_42DA4C3316FE72E1');
        $this->addSql('ALTER TABLE job_announcement DROP FOREIGN KEY FK_42DA4C33DE12AB56');
        $this->addSql('ALTER TABLE job_announcement ADD CONSTRAINT FK_42DA4C3316FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE job_announcement ADD CONSTRAINT FK_42DA4C33DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE job_title DROP FOREIGN KEY FK_2A6AC51B16FE72E1');
        $this->addSql('ALTER TABLE job_title DROP FOREIGN KEY FK_2A6AC51BDE12AB56');
        $this->addSql('ALTER TABLE job_title ADD CONSTRAINT FK_2A6AC51B16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE job_title ADD CONSTRAINT FK_2A6AC51BDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE resume DROP FOREIGN KEY FK_60C1D0A016FE72E1');
        $this->addSql('ALTER TABLE resume DROP FOREIGN KEY FK_60C1D0A0DE12AB56');
        $this->addSql('ALTER TABLE resume ADD CONSTRAINT FK_60C1D0A016FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE resume ADD CONSTRAINT FK_60C1D0A0DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D316FE72E1');
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D3DE12AB56');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D316FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE subscription_plan DROP FOREIGN KEY FK_EA664B6316FE72E1');
        $this->addSql('ALTER TABLE subscription_plan DROP FOREIGN KEY FK_EA664B63DE12AB56');
        $this->addSql('ALTER TABLE subscription_plan ADD CONSTRAINT FK_EA664B6316FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE subscription_plan ADD CONSTRAINT FK_EA664B63DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE city_upload DROP FOREIGN KEY FK_E2F1A8EF16FE72E1');
        $this->addSql('ALTER TABLE city_upload DROP FOREIGN KEY FK_E2F1A8EFDE12AB56');
        $this->addSql('ALTER TABLE city_upload ADD CONSTRAINT FK_E2F1A8EF16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE city_upload ADD CONSTRAINT FK_E2F1A8EFDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE job_title_upload DROP FOREIGN KEY FK_D67F661916FE72E1');
        $this->addSql('ALTER TABLE job_title_upload DROP FOREIGN KEY FK_D67F6619DE12AB56');
        $this->addSql('ALTER TABLE job_title_upload ADD CONSTRAINT FK_D67F661916FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE job_title_upload ADD CONSTRAINT FK_D67F6619DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE city_profile_upload DROP FOREIGN KEY FK_6C868E2C16FE72E1');
        $this->addSql('ALTER TABLE city_profile_upload DROP FOREIGN KEY FK_6C868E2CDE12AB56');
        $this->addSql('ALTER TABLE city_profile_upload ADD CONSTRAINT FK_6C868E2C16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE city_profile_upload ADD CONSTRAINT FK_6C868E2CDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE city_registration DROP FOREIGN KEY FK_F2340E2816FE72E1');
        $this->addSql('ALTER TABLE city_registration DROP FOREIGN KEY FK_F2340E28DE12AB56');
        $this->addSql('ALTER TABLE city_registration ADD CONSTRAINT FK_F2340E2816FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE city_registration ADD CONSTRAINT FK_F2340E28DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE license_certification DROP FOREIGN KEY FK_AE740F2316FE72E1');
        $this->addSql('ALTER TABLE license_certification DROP FOREIGN KEY FK_AE740F23DE12AB56');
        $this->addSql('ALTER TABLE license_certification ADD CONSTRAINT FK_AE740F2316FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE license_certification ADD CONSTRAINT FK_AE740F23DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE education DROP FOREIGN KEY FK_DB0A5ED216FE72E1');
        $this->addSql('ALTER TABLE education DROP FOREIGN KEY FK_DB0A5ED2DE12AB56');
        $this->addSql('ALTER TABLE education ADD CONSTRAINT FK_DB0A5ED216FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE education ADD CONSTRAINT FK_DB0A5ED2DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE work_history DROP FOREIGN KEY FK_F271C86916FE72E1');
        $this->addSql('ALTER TABLE work_history DROP FOREIGN KEY FK_F271C869DE12AB56');
        $this->addSql('ALTER TABLE work_history ADD CONSTRAINT FK_F271C86916FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE work_history ADD CONSTRAINT FK_F271C869DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE city DROP FOREIGN KEY FK_2D5B0234DE12AB56');
        $this->addSql('ALTER TABLE city DROP FOREIGN KEY FK_2D5B023416FE72E1');
        $this->addSql('ALTER TABLE city ADD CONSTRAINT FK_2D5B0234DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE city ADD CONSTRAINT FK_2D5B023416FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE city_profile_upload DROP FOREIGN KEY FK_6C868E2CDE12AB56');
        $this->addSql('ALTER TABLE city_profile_upload DROP FOREIGN KEY FK_6C868E2C16FE72E1');
        $this->addSql('ALTER TABLE city_profile_upload ADD CONSTRAINT FK_6C868E2CDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE city_profile_upload ADD CONSTRAINT FK_6C868E2C16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE city_registration DROP FOREIGN KEY FK_F2340E28DE12AB56');
        $this->addSql('ALTER TABLE city_registration DROP FOREIGN KEY FK_F2340E2816FE72E1');
        $this->addSql('ALTER TABLE city_registration ADD CONSTRAINT FK_F2340E28DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE city_registration ADD CONSTRAINT FK_F2340E2816FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE city_upload DROP FOREIGN KEY FK_E2F1A8EFDE12AB56');
        $this->addSql('ALTER TABLE city_upload DROP FOREIGN KEY FK_E2F1A8EF16FE72E1');
        $this->addSql('ALTER TABLE city_upload ADD CONSTRAINT FK_E2F1A8EFDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE city_upload ADD CONSTRAINT FK_E2F1A8EF16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE education DROP FOREIGN KEY FK_DB0A5ED2DE12AB56');
        $this->addSql('ALTER TABLE education DROP FOREIGN KEY FK_DB0A5ED216FE72E1');
        $this->addSql('ALTER TABLE education ADD CONSTRAINT FK_DB0A5ED2DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE education ADD CONSTRAINT FK_DB0A5ED216FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE job_announcement DROP FOREIGN KEY FK_42DA4C33DE12AB56');
        $this->addSql('ALTER TABLE job_announcement DROP FOREIGN KEY FK_42DA4C3316FE72E1');
        $this->addSql('ALTER TABLE job_announcement ADD CONSTRAINT FK_42DA4C33DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE job_announcement ADD CONSTRAINT FK_42DA4C3316FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE job_title DROP FOREIGN KEY FK_2A6AC51BDE12AB56');
        $this->addSql('ALTER TABLE job_title DROP FOREIGN KEY FK_2A6AC51B16FE72E1');
        $this->addSql('ALTER TABLE job_title ADD CONSTRAINT FK_2A6AC51BDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE job_title ADD CONSTRAINT FK_2A6AC51B16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE job_title_upload DROP FOREIGN KEY FK_D67F6619DE12AB56');
        $this->addSql('ALTER TABLE job_title_upload DROP FOREIGN KEY FK_D67F661916FE72E1');
        $this->addSql('ALTER TABLE job_title_upload ADD CONSTRAINT FK_D67F6619DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE job_title_upload ADD CONSTRAINT FK_D67F661916FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE license_certification DROP FOREIGN KEY FK_AE740F23DE12AB56');
        $this->addSql('ALTER TABLE license_certification DROP FOREIGN KEY FK_AE740F2316FE72E1');
        $this->addSql('ALTER TABLE license_certification ADD CONSTRAINT FK_AE740F23DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE license_certification ADD CONSTRAINT FK_AE740F2316FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE resume DROP FOREIGN KEY FK_60C1D0A0DE12AB56');
        $this->addSql('ALTER TABLE resume DROP FOREIGN KEY FK_60C1D0A016FE72E1');
        $this->addSql('ALTER TABLE resume ADD CONSTRAINT FK_60C1D0A0DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE resume ADD CONSTRAINT FK_60C1D0A016FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D3DE12AB56');
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D316FE72E1');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D316FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE subscription_plan DROP FOREIGN KEY FK_EA664B63DE12AB56');
        $this->addSql('ALTER TABLE subscription_plan DROP FOREIGN KEY FK_EA664B6316FE72E1');
        $this->addSql('ALTER TABLE subscription_plan ADD CONSTRAINT FK_EA664B63DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE subscription_plan ADD CONSTRAINT FK_EA664B6316FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE work_history DROP FOREIGN KEY FK_F271C869DE12AB56');
        $this->addSql('ALTER TABLE work_history DROP FOREIGN KEY FK_F271C86916FE72E1');
        $this->addSql('ALTER TABLE work_history ADD CONSTRAINT FK_F271C869DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE work_history ADD CONSTRAINT FK_F271C86916FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
    }
}
