<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240929141816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE url ADD is_sent TINYINT(1) NOT NULL, CHANGE url url VARCHAR(255) NOT NULL, CHANGE hash hash VARCHAR(14) NOT NULL, CHANGE created_date created_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE url DROP is_sent, CHANGE url url VARCHAR(255) DEFAULT \'\' NOT NULL, CHANGE hash hash VARCHAR(14) DEFAULT \'\' NOT NULL, CHANGE created_date created_date DATETIME NOT NULL');
    }
}
