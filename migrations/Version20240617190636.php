<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240617190636 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bank_account (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, account_number VARCHAR(23) DEFAULT NULL, added_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bank_reconciliation (id INT AUTO_INCREMENT NOT NULL, bank_account_id INT NOT NULL, operation_date DATE NOT NULL, label VARCHAR(255) NOT NULL, credit NUMERIC(10, 2) DEFAULT NULL, debit NUMERIC(10, 2) DEFAULT NULL, INDEX IDX_8B312F2212CB990C (bank_account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bank_reconciliation ADD CONSTRAINT FK_8B312F2212CB990C FOREIGN KEY (bank_account_id) REFERENCES bank_account (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bank_reconciliation DROP FOREIGN KEY FK_8B312F2212CB990C');
        $this->addSql('DROP TABLE bank_account');
        $this->addSql('DROP TABLE bank_reconciliation');
    }
}
