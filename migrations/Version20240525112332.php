<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240525112332 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sale (id INT AUTO_INCREMENT NOT NULL, registered_by_id INT NOT NULL, item_id INT NOT NULL, recorded_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', qty DOUBLE PRECISION NOT NULL, qty_returned DOUBLE PRECISION DEFAULT NULL, received TINYINT(1) DEFAULT NULL, INDEX IDX_E54BC00527E92E18 (registered_by_id), INDEX IDX_E54BC005126F525E (item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sale ADD CONSTRAINT FK_E54BC00527E92E18 FOREIGN KEY (registered_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sale ADD CONSTRAINT FK_E54BC005126F525E FOREIGN KEY (item_id) REFERENCES article (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sale DROP FOREIGN KEY FK_E54BC00527E92E18');
        $this->addSql('ALTER TABLE sale DROP FOREIGN KEY FK_E54BC005126F525E');
        $this->addSql('DROP TABLE sale');
    }
}
