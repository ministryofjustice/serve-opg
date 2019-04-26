<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190327111207 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $result =  $this->connection
            ->fetchColumn("SELECT exists (SELECT * FROM dc_user WHERE dc_user.email = 'thomas.withers@digital.justice.gov.uk' LIMIT 1)");

        if (!$result) {
            $this->addSql("INSERT INTO dc_user VALUES('15', 'thomas.withers@digital.justice.gov.uk','set-me-up')");
        }
        $result =  $this->connection
            ->fetchColumn("SELECT exists (SELECT * FROM dc_user WHERE dc_user.email = 'kass.asgher@justice.gov.uk' LIMIT 1)");

        if (!$result) {
            $this->addSql("INSERT INTO dc_user VALUES('16', 'kass.asgher@justice.gov.uk','set-me-up')");
        }

        $result =  $this->connection
            ->fetchColumn("SELECT exists (SELECT * FROM dc_user WHERE dc_user.email = 'suleman.patel@justice.gov.uk' LIMIT 1)");

        if (!$result) {
            $this->addSql("INSERT INTO dc_user VALUES('17', 'suleman.patel@justice.gov.uk','set-me-up')");
        }

        $result =  $this->connection
            ->fetchColumn("SELECT exists (SELECT * FROM dc_user WHERE dc_user.email = 'raliat.odusanya@justice.gov.uk' LIMIT 1)");

        if (!$result) {
            $this->addSql("INSERT INTO dc_user VALUES('18', 'raliat.odusanya@justice.gov.uk','set-me-up')");
        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DELETE FROM dc_user WHERE email = 'thomas.withers@digital.justice.gov.uk';");
        $this->addSql("DELETE FROM dc_user WHERE email = 'kass.asgher@justice.gov.uk';");
        $this->addSql("DELETE FROM dc_user WHERE email = 'suleman.patel@justice.gov.uk';");
        $this->addSql("DELETE FROM dc_user WHERE email = 'raliat.odusanya@justice.gov.uk';");
    }
}
