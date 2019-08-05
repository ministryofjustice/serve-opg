<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to assign admin roles and create new admin accounts for new merged team
 */
final class Version20190729163115 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $sql = <<<SQL
UPDATE dc_user SET roles = 'a:1:{i:0;s:10:\"ROLE_ADMIN\";}'
WHERE email = 'alex.saunders@digital.justice.gov.uk'
OR email = 'paul.mcqueen@digital.justice.gov.uk'
OR email = 'shaun.lizzio@digital.justice.gov.uk'
OR email = 'elizabeth.feenan@digital.justice.gov.uk'
SQL;
        $this->addSql($sql);

        $now = date("Y-m-d h:m:s");

        $result =  $this->connection
            ->fetchColumn("SELECT count(*) FROM dc_user WHERE dc_user.email = 'alex.eves@digital.justice.gov.uk'");

        if (!$result) {
            $this->addSql("INSERT INTO dc_user (id, email, password, created_at, roles) VALUES(nextval('dc_user_id_seq'), 'alex.eves@digital.justice.gov.uk', 'set-me-up', '${now}', 'a:1:{i:0;s:10:\"ROLE_ADMIN\";}')");
        }

        $result =  $this->connection
            ->fetchColumn("SELECT count(*) FROM dc_user WHERE dc_user.email = 'greg.tyler@digital.justice.gov.uk'");

        if (!$result) {
            $this->addSql("INSERT INTO dc_user (id, email, password, created_at, roles) VALUES(nextval('dc_user_id_seq'), 'greg.tyler@digital.justice.gov.uk', 'set-me-up', '${now}', 'a:1:{i:0;s:10:\"ROLE_ADMIN\";}')");
        }

        $result =  $this->connection
            ->fetchColumn("SELECT count(*) FROM dc_user WHERE dc_user.email = 'stacey.cook@digital.justice.gov.uk'");

        if (!$result) {
            $this->addSql("INSERT INTO dc_user (id, email, password, created_at, roles) VALUES(nextval('dc_user_id_seq'), 'stacey.cook@digital.justice.gov.uk', 'set-me-up', '${now}', 'a:1:{i:0;s:10:\"ROLE_ADMIN\";}')");
        }

    }

    public function down(Schema $schema) : void
    {
        $sql = <<<SQL
UPDATE dc_user SET roles = 'a:0:{}'
WHERE email = 'alex.saunders@digital.justice.gov.uk'
OR email = 'paul.mcqueen@digital.justice.gov.uk'
OR email = 'shaun.lizzio@digital.justice.gov.uk'
OR email = 'elizabeth.feenan@digital.justice.gov.uk'
SQL;
        $this->addSql($sql);

        $this->addSql("DELETE FROM dc_user WHERE email = 'alex.eves@digital.justice.gov.uk';");
        $this->addSql("DELETE FROM dc_user WHERE email = 'greg.tyler@digital.justice.gov.uk';");
        $this->addSql("DELETE FROM dc_user WHERE email = 'stacey.cook@digital.justice.gov.uk';");
    }
}
