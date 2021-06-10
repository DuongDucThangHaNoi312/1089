<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190406162231 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job_title DROP FOREIGN KEY FK_2A6AC51B5FB14BA7');
        $this->addSql('ALTER TABLE job_title DROP FOREIGN KEY FK_2A6AC51B8BAC62AF');
        $this->addSql('ALTER TABLE job_title DROP FOREIGN KEY FK_2A6AC51BC54C8C93');
        $this->addSql('ALTER TABLE job_title ADD CONSTRAINT FK_2A6AC51B5FB14BA7 FOREIGN KEY (level_id) REFERENCES job_level (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE job_title ADD CONSTRAINT FK_2A6AC51B8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_title ADD CONSTRAINT FK_2A6AC51BC54C8C93 FOREIGN KEY (type_id) REFERENCES job_type (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE city DROP FOREIGN KEY FK_2D5B023416F2520F');
        $this->addSql('ALTER TABLE city DROP FOREIGN KEY FK_2D5B02346367CF2A');
        $this->addSql('ALTER TABLE city DROP FOREIGN KEY FK_2D5B02349A1887DC');
        $this->addSql('ALTER TABLE city ADD CONSTRAINT FK_2D5B023416F2520F FOREIGN KEY (profile_type_id) REFERENCES profile_type (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE city ADD CONSTRAINT FK_2D5B02346367CF2A FOREIGN KEY (admin_city_user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE city ADD CONSTRAINT FK_2D5B02349A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE job_announcement DROP FOREIGN KEY FK_42DA4C336BF700BD');
        $this->addSql('ALTER TABLE job_announcement DROP FOREIGN KEY FK_42DA4C336DD822C6');
        $this->addSql('ALTER TABLE job_announcement DROP FOREIGN KEY FK_42DA4C33941D52DE');
        $this->addSql('ALTER TABLE job_announcement DROP FOREIGN KEY FK_42DA4C33F4BD7827');
        $this->addSql('ALTER TABLE job_announcement ADD CONSTRAINT FK_42DA4C336BF700BD FOREIGN KEY (status_id) REFERENCES job_announcement_status (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE job_announcement ADD CONSTRAINT FK_42DA4C336DD822C6 FOREIGN KEY (job_title_id) REFERENCES job_title (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_announcement ADD CONSTRAINT FK_42DA4C33941D52DE FOREIGN KEY (wage_salary_unit_id) REFERENCES wage_salary_unit (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE job_announcement ADD CONSTRAINT FK_42DA4C33F4BD7827 FOREIGN KEY (assigned_to_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE url DROP FOREIGN KEY FK_F47645AE8BAC62AF');
        $this->addSql('ALTER TABLE url ADD CONSTRAINT FK_F47645AE8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE division DROP FOREIGN KEY FK_101747148BAC62AF');
        $this->addSql('ALTER TABLE division ADD CONSTRAINT FK_101747148BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE saved_search DROP FOREIGN KEY FK_D0F6A0BCA76ED395');
        $this->addSql('ALTER TABLE saved_search ADD CONSTRAINT FK_D0F6A0BCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6495D83CC1');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6496DD822C6');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64985E73F45');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6498BAC62AF');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649ADDA6322');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649AE80F5DF');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649E3BF5E52');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6495D83CC1 FOREIGN KEY (state_id) REFERENCES state (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6496DD822C6 FOREIGN KEY (job_title_id) REFERENCES job_title (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64985E73F45 FOREIGN KEY (county_id) REFERENCES county (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6498BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649ADDA6322 FOREIGN KEY (interested_job_type_id) REFERENCES job_type (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649E3BF5E52 FOREIGN KEY (interested_job_level_id) REFERENCES job_level (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D35A8B1553');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D35A8B1553 FOREIGN KEY (job_seeker_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE saved_resume DROP FOREIGN KEY FK_4C7ABBB1A266DC7');
        $this->addSql('ALTER TABLE saved_resume DROP FOREIGN KEY FK_4C7ABBBD262AF09');
        $this->addSql('ALTER TABLE saved_resume ADD CONSTRAINT FK_4C7ABBB1A266DC7 FOREIGN KEY (city_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE saved_resume ADD CONSTRAINT FK_4C7ABBBD262AF09 FOREIGN KEY (resume_id) REFERENCES resume (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE saved_city DROP FOREIGN KEY FK_2364F0218BAC62AF');
        $this->addSql('ALTER TABLE saved_city DROP FOREIGN KEY FK_2364F021A76ED395');
        $this->addSql('ALTER TABLE saved_city ADD CONSTRAINT FK_2364F0218BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE saved_city ADD CONSTRAINT FK_2364F021A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE saved_job_announcement DROP FOREIGN KEY FK_56F40A555A8B1553');
        $this->addSql('ALTER TABLE saved_job_announcement DROP FOREIGN KEY FK_56F40A557AA6A578');
        $this->addSql('ALTER TABLE saved_job_announcement ADD CONSTRAINT FK_56F40A555A8B1553 FOREIGN KEY (job_seeker_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE saved_job_announcement ADD CONSTRAINT FK_56F40A557AA6A578 FOREIGN KEY (job_announcement_id) REFERENCES job_announcement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resume DROP FOREIGN KEY FK_60C1D0A0C2C5BAA3');
        $this->addSql('ALTER TABLE resume DROP FOREIGN KEY FK_60C1D0A0E3BF5E52');
        $this->addSql('ALTER TABLE resume ADD CONSTRAINT FK_60C1D0A0C2C5BAA3 FOREIGN KEY (job_seeker_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resume ADD CONSTRAINT FK_60C1D0A0E3BF5E52 FOREIGN KEY (interested_job_level_id) REFERENCES job_level (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE saved_job_title DROP FOREIGN KEY FK_1FB18CE65A8B1553');
        $this->addSql('ALTER TABLE saved_job_title DROP FOREIGN KEY FK_1FB18CE66DD822C6');
        $this->addSql('ALTER TABLE saved_job_title ADD CONSTRAINT FK_1FB18CE65A8B1553 FOREIGN KEY (job_seeker_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE saved_job_title ADD CONSTRAINT FK_1FB18CE66DD822C6 FOREIGN KEY (job_title_id) REFERENCES job_title (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE department DROP FOREIGN KEY FK_CD1DE18A8BAC62AF');
        $this->addSql('ALTER TABLE department ADD CONSTRAINT FK_CD1DE18A8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE submitted_job_title_interest DROP FOREIGN KEY FK_B2ECE1045A8B1553');
        $this->addSql('ALTER TABLE submitted_job_title_interest DROP FOREIGN KEY FK_B2ECE1046DD822C6');
        $this->addSql('ALTER TABLE submitted_job_title_interest ADD CONSTRAINT FK_B2ECE1045A8B1553 FOREIGN KEY (job_seeker_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE submitted_job_title_interest ADD CONSTRAINT FK_B2ECE1046DD822C6 FOREIGN KEY (job_title_id) REFERENCES job_title (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE price_schedule DROP FOREIGN KEY FK_388E2AEC9B8CE200');
        $this->addSql('ALTER TABLE price_schedule ADD CONSTRAINT FK_388E2AEC9B8CE200 FOREIGN KEY (subscription_plan_id) REFERENCES subscription_plan (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE census_population DROP FOREIGN KEY FK_DB411BA18BAC62AF');
        $this->addSql('ALTER TABLE census_population ADD CONSTRAINT FK_DB411BA18BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE county DROP FOREIGN KEY FK_58E2FF255D83CC1');
        $this->addSql('ALTER TABLE county ADD CONSTRAINT FK_58E2FF255D83CC1 FOREIGN KEY (state_id) REFERENCES state (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE operation_hours DROP FOREIGN KEY FK_D3D6E028BAC62AF');
        $this->addSql('ALTER TABLE operation_hours ADD CONSTRAINT FK_D3D6E028BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE city_city_user DROP FOREIGN KEY FK_CA89F7341A266DC7');
        $this->addSql('ALTER TABLE city_city_user DROP FOREIGN KEY FK_CA89F7348BAC62AF');
        $this->addSql('ALTER TABLE city_city_user ADD CONSTRAINT FK_CA89F7341A266DC7 FOREIGN KEY (city_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE city_city_user ADD CONSTRAINT FK_CA89F7348BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE city_registration DROP FOREIGN KEY FK_F2340E281A266DC7');
        $this->addSql('ALTER TABLE city_registration DROP FOREIGN KEY FK_F2340E286BF700BD');
        $this->addSql('ALTER TABLE city_registration DROP FOREIGN KEY FK_F2340E288BAC62AF');
        $this->addSql('ALTER TABLE city_registration ADD CONSTRAINT FK_F2340E281A266DC7 FOREIGN KEY (city_user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE city_registration ADD CONSTRAINT FK_F2340E286BF700BD FOREIGN KEY (status_id) REFERENCES city_registration_status (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE city_registration ADD CONSTRAINT FK_F2340E288BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE license_certification DROP FOREIGN KEY FK_AE740F23D262AF09');
        $this->addSql('ALTER TABLE license_certification ADD CONSTRAINT FK_AE740F23D262AF09 FOREIGN KEY (resume_id) REFERENCES resume (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE education DROP FOREIGN KEY FK_DB0A5ED2588EC755');
        $this->addSql('ALTER TABLE education DROP FOREIGN KEY FK_DB0A5ED2D262AF09');
        $this->addSql('ALTER TABLE education CHANGE degree_type_id degree_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE education ADD CONSTRAINT FK_DB0A5ED2588EC755 FOREIGN KEY (degree_type_id) REFERENCES degree_type (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE education ADD CONSTRAINT FK_DB0A5ED2D262AF09 FOREIGN KEY (resume_id) REFERENCES resume (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE work_history DROP FOREIGN KEY FK_F271C869D262AF09');
        $this->addSql('ALTER TABLE work_history ADD CONSTRAINT FK_F271C869D262AF09 FOREIGN KEY (resume_id) REFERENCES resume (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_announcement_view DROP FOREIGN KEY FK_D6CA63275A8B1553');
        $this->addSql('ALTER TABLE job_announcement_view DROP FOREIGN KEY FK_D6CA63277AA6A578');
        $this->addSql('ALTER TABLE job_announcement_view ADD CONSTRAINT FK_D6CA63275A8B1553 FOREIGN KEY (job_seeker_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_announcement_view ADD CONSTRAINT FK_D6CA63277AA6A578 FOREIGN KEY (job_announcement_id) REFERENCES job_announcement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dismissed_job_announcement DROP FOREIGN KEY FK_6BA13B0B5A8B1553');
        $this->addSql('ALTER TABLE dismissed_job_announcement DROP FOREIGN KEY FK_6BA13B0B7AA6A578');
        $this->addSql('ALTER TABLE dismissed_job_announcement ADD CONSTRAINT FK_6BA13B0B5A8B1553 FOREIGN KEY (job_seeker_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dismissed_job_announcement ADD CONSTRAINT FK_6BA13B0B7AA6A578 FOREIGN KEY (job_announcement_id) REFERENCES job_announcement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dismissed_job_title DROP FOREIGN KEY FK_31FDF2AF5A8B1553');
        $this->addSql('ALTER TABLE dismissed_job_title DROP FOREIGN KEY FK_31FDF2AF6DD822C6');
        $this->addSql('ALTER TABLE dismissed_job_title ADD CONSTRAINT FK_31FDF2AF5A8B1553 FOREIGN KEY (job_seeker_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dismissed_job_title ADD CONSTRAINT FK_31FDF2AF6DD822C6 FOREIGN KEY (job_title_id) REFERENCES job_title (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE census_population DROP FOREIGN KEY FK_DB411BA18BAC62AF');
        $this->addSql('ALTER TABLE census_population ADD CONSTRAINT FK_DB411BA18BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE city DROP FOREIGN KEY FK_2D5B023416F2520F');
        $this->addSql('ALTER TABLE city DROP FOREIGN KEY FK_2D5B02349A1887DC');
        $this->addSql('ALTER TABLE city DROP FOREIGN KEY FK_2D5B02346367CF2A');
        $this->addSql('ALTER TABLE city ADD CONSTRAINT FK_2D5B023416F2520F FOREIGN KEY (profile_type_id) REFERENCES profile_type (id)');
        $this->addSql('ALTER TABLE city ADD CONSTRAINT FK_2D5B02349A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription (id)');
        $this->addSql('ALTER TABLE city ADD CONSTRAINT FK_2D5B02346367CF2A FOREIGN KEY (admin_city_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE city_city_user DROP FOREIGN KEY FK_CA89F7348BAC62AF');
        $this->addSql('ALTER TABLE city_city_user DROP FOREIGN KEY FK_CA89F7341A266DC7');
        $this->addSql('ALTER TABLE city_city_user ADD CONSTRAINT FK_CA89F7348BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE city_city_user ADD CONSTRAINT FK_CA89F7341A266DC7 FOREIGN KEY (city_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE city_registration DROP FOREIGN KEY FK_F2340E281A266DC7');
        $this->addSql('ALTER TABLE city_registration DROP FOREIGN KEY FK_F2340E288BAC62AF');
        $this->addSql('ALTER TABLE city_registration DROP FOREIGN KEY FK_F2340E286BF700BD');
        $this->addSql('ALTER TABLE city_registration ADD CONSTRAINT FK_F2340E281A266DC7 FOREIGN KEY (city_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE city_registration ADD CONSTRAINT FK_F2340E288BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE city_registration ADD CONSTRAINT FK_F2340E286BF700BD FOREIGN KEY (status_id) REFERENCES city_registration_status (id)');
        $this->addSql('ALTER TABLE county DROP FOREIGN KEY FK_58E2FF255D83CC1');
        $this->addSql('ALTER TABLE county ADD CONSTRAINT FK_58E2FF255D83CC1 FOREIGN KEY (state_id) REFERENCES state (id)');
        $this->addSql('ALTER TABLE department DROP FOREIGN KEY FK_CD1DE18A8BAC62AF');
        $this->addSql('ALTER TABLE department ADD CONSTRAINT FK_CD1DE18A8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE dismissed_job_announcement DROP FOREIGN KEY FK_6BA13B0B5A8B1553');
        $this->addSql('ALTER TABLE dismissed_job_announcement DROP FOREIGN KEY FK_6BA13B0B7AA6A578');
        $this->addSql('ALTER TABLE dismissed_job_announcement ADD CONSTRAINT FK_6BA13B0B5A8B1553 FOREIGN KEY (job_seeker_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE dismissed_job_announcement ADD CONSTRAINT FK_6BA13B0B7AA6A578 FOREIGN KEY (job_announcement_id) REFERENCES job_announcement (id)');
        $this->addSql('ALTER TABLE dismissed_job_title DROP FOREIGN KEY FK_31FDF2AF5A8B1553');
        $this->addSql('ALTER TABLE dismissed_job_title DROP FOREIGN KEY FK_31FDF2AF6DD822C6');
        $this->addSql('ALTER TABLE dismissed_job_title ADD CONSTRAINT FK_31FDF2AF5A8B1553 FOREIGN KEY (job_seeker_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE dismissed_job_title ADD CONSTRAINT FK_31FDF2AF6DD822C6 FOREIGN KEY (job_title_id) REFERENCES job_title (id)');
        $this->addSql('ALTER TABLE division DROP FOREIGN KEY FK_101747148BAC62AF');
        $this->addSql('ALTER TABLE division ADD CONSTRAINT FK_101747148BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE education DROP FOREIGN KEY FK_DB0A5ED2588EC755');
        $this->addSql('ALTER TABLE education DROP FOREIGN KEY FK_DB0A5ED2D262AF09');
        $this->addSql('ALTER TABLE education CHANGE degree_type_id degree_type_id INT NOT NULL');
        $this->addSql('ALTER TABLE education ADD CONSTRAINT FK_DB0A5ED2588EC755 FOREIGN KEY (degree_type_id) REFERENCES degree_type (id)');
        $this->addSql('ALTER TABLE education ADD CONSTRAINT FK_DB0A5ED2D262AF09 FOREIGN KEY (resume_id) REFERENCES resume (id)');
        $this->addSql('ALTER TABLE job_announcement DROP FOREIGN KEY FK_42DA4C336DD822C6');
        $this->addSql('ALTER TABLE job_announcement DROP FOREIGN KEY FK_42DA4C33941D52DE');
        $this->addSql('ALTER TABLE job_announcement DROP FOREIGN KEY FK_42DA4C336BF700BD');
        $this->addSql('ALTER TABLE job_announcement DROP FOREIGN KEY FK_42DA4C33F4BD7827');
        $this->addSql('ALTER TABLE job_announcement ADD CONSTRAINT FK_42DA4C336DD822C6 FOREIGN KEY (job_title_id) REFERENCES job_title (id)');
        $this->addSql('ALTER TABLE job_announcement ADD CONSTRAINT FK_42DA4C33941D52DE FOREIGN KEY (wage_salary_unit_id) REFERENCES wage_salary_unit (id)');
        $this->addSql('ALTER TABLE job_announcement ADD CONSTRAINT FK_42DA4C336BF700BD FOREIGN KEY (status_id) REFERENCES job_announcement_status (id)');
        $this->addSql('ALTER TABLE job_announcement ADD CONSTRAINT FK_42DA4C33F4BD7827 FOREIGN KEY (assigned_to_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE job_announcement_view DROP FOREIGN KEY FK_D6CA63277AA6A578');
        $this->addSql('ALTER TABLE job_announcement_view DROP FOREIGN KEY FK_D6CA63275A8B1553');
        $this->addSql('ALTER TABLE job_announcement_view ADD CONSTRAINT FK_D6CA63277AA6A578 FOREIGN KEY (job_announcement_id) REFERENCES job_announcement (id)');
        $this->addSql('ALTER TABLE job_announcement_view ADD CONSTRAINT FK_D6CA63275A8B1553 FOREIGN KEY (job_seeker_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE job_title DROP FOREIGN KEY FK_2A6AC51B5FB14BA7');
        $this->addSql('ALTER TABLE job_title DROP FOREIGN KEY FK_2A6AC51BC54C8C93');
        $this->addSql('ALTER TABLE job_title DROP FOREIGN KEY FK_2A6AC51B8BAC62AF');
        $this->addSql('ALTER TABLE job_title ADD CONSTRAINT FK_2A6AC51B5FB14BA7 FOREIGN KEY (level_id) REFERENCES job_level (id)');
        $this->addSql('ALTER TABLE job_title ADD CONSTRAINT FK_2A6AC51BC54C8C93 FOREIGN KEY (type_id) REFERENCES job_type (id)');
        $this->addSql('ALTER TABLE job_title ADD CONSTRAINT FK_2A6AC51B8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE license_certification DROP FOREIGN KEY FK_AE740F23D262AF09');
        $this->addSql('ALTER TABLE license_certification ADD CONSTRAINT FK_AE740F23D262AF09 FOREIGN KEY (resume_id) REFERENCES resume (id)');
        $this->addSql('ALTER TABLE operation_hours DROP FOREIGN KEY FK_D3D6E028BAC62AF');
        $this->addSql('ALTER TABLE operation_hours ADD CONSTRAINT FK_D3D6E028BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE price_schedule DROP FOREIGN KEY FK_388E2AEC9B8CE200');
        $this->addSql('ALTER TABLE price_schedule ADD CONSTRAINT FK_388E2AEC9B8CE200 FOREIGN KEY (subscription_plan_id) REFERENCES subscription_plan (id)');
        $this->addSql('ALTER TABLE resume DROP FOREIGN KEY FK_60C1D0A0C2C5BAA3');
        $this->addSql('ALTER TABLE resume DROP FOREIGN KEY FK_60C1D0A0E3BF5E52');
        $this->addSql('ALTER TABLE resume ADD CONSTRAINT FK_60C1D0A0C2C5BAA3 FOREIGN KEY (job_seeker_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE resume ADD CONSTRAINT FK_60C1D0A0E3BF5E52 FOREIGN KEY (interested_job_level_id) REFERENCES job_level (id)');
        $this->addSql('ALTER TABLE saved_city DROP FOREIGN KEY FK_2364F021A76ED395');
        $this->addSql('ALTER TABLE saved_city DROP FOREIGN KEY FK_2364F0218BAC62AF');
        $this->addSql('ALTER TABLE saved_city ADD CONSTRAINT FK_2364F021A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE saved_city ADD CONSTRAINT FK_2364F0218BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE saved_job_announcement DROP FOREIGN KEY FK_56F40A555A8B1553');
        $this->addSql('ALTER TABLE saved_job_announcement DROP FOREIGN KEY FK_56F40A557AA6A578');
        $this->addSql('ALTER TABLE saved_job_announcement ADD CONSTRAINT FK_56F40A555A8B1553 FOREIGN KEY (job_seeker_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE saved_job_announcement ADD CONSTRAINT FK_56F40A557AA6A578 FOREIGN KEY (job_announcement_id) REFERENCES job_announcement (id)');
        $this->addSql('ALTER TABLE saved_job_title DROP FOREIGN KEY FK_1FB18CE65A8B1553');
        $this->addSql('ALTER TABLE saved_job_title DROP FOREIGN KEY FK_1FB18CE66DD822C6');
        $this->addSql('ALTER TABLE saved_job_title ADD CONSTRAINT FK_1FB18CE65A8B1553 FOREIGN KEY (job_seeker_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE saved_job_title ADD CONSTRAINT FK_1FB18CE66DD822C6 FOREIGN KEY (job_title_id) REFERENCES job_title (id)');
        $this->addSql('ALTER TABLE saved_resume DROP FOREIGN KEY FK_4C7ABBB1A266DC7');
        $this->addSql('ALTER TABLE saved_resume DROP FOREIGN KEY FK_4C7ABBBD262AF09');
        $this->addSql('ALTER TABLE saved_resume ADD CONSTRAINT FK_4C7ABBB1A266DC7 FOREIGN KEY (city_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE saved_resume ADD CONSTRAINT FK_4C7ABBBD262AF09 FOREIGN KEY (resume_id) REFERENCES resume (id)');
        $this->addSql('ALTER TABLE saved_search DROP FOREIGN KEY FK_D0F6A0BCA76ED395');
        $this->addSql('ALTER TABLE saved_search ADD CONSTRAINT FK_D0F6A0BCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE submitted_job_title_interest DROP FOREIGN KEY FK_B2ECE1046DD822C6');
        $this->addSql('ALTER TABLE submitted_job_title_interest DROP FOREIGN KEY FK_B2ECE1045A8B1553');
        $this->addSql('ALTER TABLE submitted_job_title_interest ADD CONSTRAINT FK_B2ECE1046DD822C6 FOREIGN KEY (job_title_id) REFERENCES job_title (id)');
        $this->addSql('ALTER TABLE submitted_job_title_interest ADD CONSTRAINT FK_B2ECE1045A8B1553 FOREIGN KEY (job_seeker_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D35A8B1553');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D35A8B1553 FOREIGN KEY (job_seeker_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE url DROP FOREIGN KEY FK_F47645AE8BAC62AF');
        $this->addSql('ALTER TABLE url ADD CONSTRAINT FK_F47645AE8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6496DD822C6');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649AE80F5DF');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6498BAC62AF');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6495D83CC1');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64985E73F45');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649ADDA6322');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649E3BF5E52');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6496DD822C6 FOREIGN KEY (job_title_id) REFERENCES job_title (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6498BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6495D83CC1 FOREIGN KEY (state_id) REFERENCES state (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64985E73F45 FOREIGN KEY (county_id) REFERENCES county (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649ADDA6322 FOREIGN KEY (interested_job_type_id) REFERENCES job_type (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649E3BF5E52 FOREIGN KEY (interested_job_level_id) REFERENCES job_level (id)');
        $this->addSql('ALTER TABLE work_history DROP FOREIGN KEY FK_F271C869D262AF09');
        $this->addSql('ALTER TABLE work_history ADD CONSTRAINT FK_F271C869D262AF09 FOREIGN KEY (resume_id) REFERENCES resume (id)');
    }
}
