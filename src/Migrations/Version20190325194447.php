<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190325194447 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE stripe_card (id INT AUTO_INCREMENT NOT NULL, address_city VARCHAR(255) DEFAULT NULL, address_country VARCHAR(255) DEFAULT NULL, address_line1 VARCHAR(255) DEFAULT NULL, address_line1_check VARCHAR(255) DEFAULT NULL, address_line2 VARCHAR(255) DEFAULT NULL, address_state VARCHAR(255) DEFAULT NULL, address_zip VARCHAR(255) DEFAULT NULL, address_zip_check VARCHAR(255) DEFAULT NULL, brand VARCHAR(25) NOT NULL, country VARCHAR(255) DEFAULT NULL, customer VARCHAR(255) DEFAULT NULL, cvc_check VARCHAR(255) DEFAULT NULL, dynamic_last_4 VARCHAR(255) DEFAULT NULL, exp_month SMALLINT NOT NULL, exp_year INT NOT NULL, fingerprint VARCHAR(255) DEFAULT NULL, funding VARCHAR(255) DEFAULT NULL, last4 VARCHAR(255) NOT NULL, crad_name VARCHAR(255) DEFAULT NULL, metadata LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', stripe_id VARCHAR(190) NOT NULL, UNIQUE INDEX stripe_id_idx (stripe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stripe_charge (id INT AUTO_INCREMENT NOT NULL, amount INT NOT NULL, amount_refunded INT NOT NULL, balance_transaction VARCHAR(255) DEFAULT NULL, captured TINYINT(1) DEFAULT NULL, created INT NOT NULL, currency VARCHAR(255) NOT NULL, customer VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, dispute VARCHAR(255) DEFAULT NULL, failure_code VARCHAR(255) DEFAULT NULL, failure_message VARCHAR(255) DEFAULT NULL, fraud_details LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', invoice VARCHAR(255) DEFAULT NULL, livemode TINYINT(1) NOT NULL, metadata LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', order_id VARCHAR(255) DEFAULT NULL, outcome LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', paid TINYINT(1) NOT NULL, receipt_email VARCHAR(255) DEFAULT NULL, receipt_number VARCHAR(255) DEFAULT NULL, refunded TINYINT(1) NOT NULL, shipping LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', source VARCHAR(255) DEFAULT NULL, statement_descriptor VARCHAR(255) DEFAULT NULL, status VARCHAR(255) NOT NULL, stripe_id VARCHAR(190) NOT NULL, UNIQUE INDEX stripe_id_idx (stripe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stripe_customer (id INT AUTO_INCREMENT NOT NULL, account_balance INT DEFAULT NULL, coupon VARCHAR(255) DEFAULT NULL, created INT NOT NULL, currency VARCHAR(255) DEFAULT NULL, default_source VARCHAR(255) DEFAULT NULL, delinquent TINYINT(1) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, livemode TINYINT(1) NOT NULL, metadata LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', shipping LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', stripe_id VARCHAR(190) NOT NULL, UNIQUE INDEX stripe_id_idx (stripe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stripe_invoice (id INT AUTO_INCREMENT NOT NULL, amount_due INT DEFAULT NULL, application_fee INT DEFAULT NULL, attempt_count INT DEFAULT NULL, attempted TINYINT(1) DEFAULT NULL, billing VARCHAR(255) DEFAULT NULL, charge VARCHAR(255) DEFAULT NULL, closed TINYINT(1) DEFAULT NULL, coupon VARCHAR(255) DEFAULT NULL, currency VARCHAR(255) NOT NULL, customer VARCHAR(255) DEFAULT NULL, invoice_date INT DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, due_date INT DEFAULT NULL, ending_balance INT DEFAULT NULL, forgiven TINYINT(1) DEFAULT NULL, invoice_lines LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', livemode TINYINT(1) NOT NULL, metadata LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', next_payment_attempt INT DEFAULT NULL, number VARCHAR(255) DEFAULT NULL, paid TINYINT(1) DEFAULT NULL, period_end INT DEFAULT NULL, period_start INT DEFAULT NULL, recipient_number VARCHAR(255) DEFAULT NULL, starting_balance INT DEFAULT NULL, statement_descriptor VARCHAR(255) DEFAULT NULL, stripe_id VARCHAR(190) NOT NULL, subscription VARCHAR(255) DEFAULT NULL, subscription_proration_date INT DEFAULT NULL, subtotal INT DEFAULT NULL, tax INT DEFAULT NULL, tax_percent NUMERIC(6, 2) DEFAULT NULL, total INT DEFAULT NULL, webhooks_delivered_at INT DEFAULT NULL, UNIQUE INDEX stripe_id_idx (stripe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stripe_plan (id INT AUTO_INCREMENT NOT NULL, amount INT NOT NULL, created INT NOT NULL, currency VARCHAR(255) NOT NULL, interval_name VARCHAR(255) NOT NULL, interval_count INT DEFAULT NULL, livemode TINYINT(1) NOT NULL, metadata LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', name VARCHAR(255) NOT NULL, statement_descriptor VARCHAR(255) DEFAULT NULL, stripe_id VARCHAR(190) NOT NULL, trial_period_days INT DEFAULT NULL, UNIQUE INDEX stripe_id_idx (stripe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stripe_refund (id INT AUTO_INCREMENT NOT NULL, amount INT NOT NULL, balance_transaction VARCHAR(255) DEFAULT NULL, charge VARCHAR(255) NOT NULL, created INT NOT NULL, currency VARCHAR(255) NOT NULL, metadata LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', failure_balance_transaction VARCHAR(255) DEFAULT NULL, failure_reason VARCHAR(255) DEFAULT NULL, reason VARCHAR(255) DEFAULT NULL, receipt_number VARCHAR(255) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, stripe_id VARCHAR(190) NOT NULL, UNIQUE INDEX stripe_id_idx (stripe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stripe_subscription (id INT AUTO_INCREMENT NOT NULL, application_fee_percent NUMERIC(10, 2) DEFAULT NULL, billing VARCHAR(255) DEFAULT NULL, cancel_at_period_end TINYINT(1) DEFAULT NULL, canceled_at INT DEFAULT NULL, coupon VARCHAR(255) DEFAULT NULL, created INT DEFAULT NULL, current_period_end INT DEFAULT NULL, current_period_start INT DEFAULT NULL, customer VARCHAR(255) DEFAULT NULL, days_until_due INT DEFAULT NULL, ended_at INT DEFAULT NULL, items LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', livemode TINYINT(1) DEFAULT NULL, metadata LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', plan VARCHAR(255) NOT NULL, quantity INT NOT NULL, start INT DEFAULT NULL, status VARCHAR(255) NOT NULL, stripe_id VARCHAR(190) NOT NULL, tax_percent NUMERIC(10, 2) DEFAULT NULL, trial_end INT DEFAULT NULL, trial_start INT DEFAULT NULL, UNIQUE INDEX stripe_id_idx (stripe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE stripe_card');
        $this->addSql('DROP TABLE stripe_charge');
        $this->addSql('DROP TABLE stripe_customer');
        $this->addSql('DROP TABLE stripe_invoice');
        $this->addSql('DROP TABLE stripe_plan');
        $this->addSql('DROP TABLE stripe_refund');
        $this->addSql('DROP TABLE stripe_subscription');
    }
}
