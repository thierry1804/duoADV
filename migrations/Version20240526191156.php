<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240526191156 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE lettrage (id INT AUTO_INCREMENT NOT NULL, recorded_by_id INT NOT NULL, recorded_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', label VARCHAR(255) NOT NULL, INDEX IDX_384EA1B0D05A957B (recorded_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lettrage ADD CONSTRAINT FK_384EA1B0D05A957B FOREIGN KEY (recorded_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE expense ADD lettrage_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE expense ADD CONSTRAINT FK_2D3A8DA68567DA27 FOREIGN KEY (lettrage_id) REFERENCES lettrage (id)');
        $this->addSql('CREATE INDEX IDX_2D3A8DA68567DA27 ON expense (lettrage_id)');
        $this->addSql('ALTER TABLE sale ADD lettrage_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sale ADD CONSTRAINT FK_E54BC0058567DA27 FOREIGN KEY (lettrage_id) REFERENCES lettrage (id)');
        $this->addSql('CREATE INDEX IDX_E54BC0058567DA27 ON sale (lettrage_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE expense DROP FOREIGN KEY FK_2D3A8DA68567DA27');
        $this->addSql('ALTER TABLE sale DROP FOREIGN KEY FK_E54BC0058567DA27');
        $this->addSql('ALTER TABLE lettrage DROP FOREIGN KEY FK_384EA1B0D05A957B');
        $this->addSql('DROP TABLE lettrage');
        $this->addSql('DROP INDEX IDX_2D3A8DA68567DA27 ON expense');
        $this->addSql('ALTER TABLE expense DROP lettrage_id');
        $this->addSql('DROP INDEX IDX_E54BC0058567DA27 ON sale');
        $this->addSql('ALTER TABLE sale DROP lettrage_id');
    }
}
