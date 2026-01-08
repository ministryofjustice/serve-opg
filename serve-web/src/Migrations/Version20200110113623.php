<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200110113623 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dc_user ADD first_name VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE dc_user ADD last_name VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE dc_user ADD phone_number VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE dc_user DROP first_name');
        $this->addSql('ALTER TABLE dc_user DROP last_name');
        $this->addSql('ALTER TABLE dc_user DROP phone_number');
    }
}
