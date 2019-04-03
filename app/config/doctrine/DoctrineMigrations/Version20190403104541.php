<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190403104541 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $result =  $this->connection
            ->fetchColumn("SELECT exists (SELECT * FROM dc_user WHERE dc_user.email = 'edward.ndubisi@justice.gov.uk' LIMIT 1)");

        if (!$result) {
            $this->addSql("INSERT INTO dc_user VALUES('19', 'edward.ndubisi@justice.gov.uk','set-me-up')");
        }
        $result =  $this->connection
            ->fetchColumn("SELECT exists (SELECT * FROM dc_user WHERE dc_user.email = 'paul.mcqueen@digital.justice.gov.uk' LIMIT 1)");

        if (!$result) {
            $this->addSql("INSERT INTO dc_user VALUES('20', 'paul.mcqueen@digital.justice.gov.uk','set-me-up')");
        }

        $result =  $this->connection
            ->fetchColumn("SELECT exists (SELECT * FROM dc_user WHERE dc_user.email = 'alex.saunders@digital.justice.gov.uk' LIMIT 1)");

        if (!$result) {
            $this->addSql("INSERT INTO dc_user VALUES('21', 'alex.saunders@digital.justice.gov.uk','set-me-up')");
        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DELETE FROM dc_user WHERE email = 'edward.ndubisi@justice.gov.uk';");
        $this->addSql("DELETE FROM dc_user WHERE email = 'paul.mcqueen@digital.justice.gov.uk';");
        $this->addSql("DELETE FROM dc_user WHERE email = 'alex.saunders@digital.justice.gov.uk';");
    }
}
