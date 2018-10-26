<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181026082427 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dc_order ALTER made_at SET DEFAULT \'01-01-2017\'');
        $this->addSql('ALTER TABLE dc_user ADD activation_token VARCHAR(40) DEFAULT NULL');
        $this->addSql('ALTER TABLE dc_user ADD activation_token_created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE dc_user DROP activation_token');
        $this->addSql('ALTER TABLE dc_user DROP activation_token_created_at');
        $this->addSql('ALTER TABLE dc_order ALTER made_at SET DEFAULT \'2017-01-01 00:00:00\'');
    }
}
