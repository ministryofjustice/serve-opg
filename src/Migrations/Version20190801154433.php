<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190801154433 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $sql = <<<SQL
UPDATE dc_user SET roles = 'a:1:{i:0;s:10:"ROLE_ADMIN";}'
WHERE email = 'alex.saunders@digital.justice.gov.uk'
OR email = 'paul.mcqueen@digital.justice.gov.uk'
OR email = 'shaun.lizzio@digital.justice.gov.uk'
OR email = 'elizabeth.feenan@digital.justice.gov.uk'
SQL;
        $this->addSql($sql);
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
    }
}
