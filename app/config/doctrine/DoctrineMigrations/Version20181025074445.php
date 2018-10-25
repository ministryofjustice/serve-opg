<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181025074445 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT INTO dc_user VALUES(1,\'elvis.ciotti@digital.justice.gov.uk\',\'$2y$12$M6AXd9I0x/hdr2rWs31HDOo1frfSJmd9Wk2WKZ9eHVSvmVgLQ8O3K\')');
        $this->addSql('INSERT INTO dc_user VALUES(2,\'sean.privett@digital.justice.gov.uk\',\'$2y$12$M6AXd9I0x/hdr2rWs31HDOo1frfSJmd9Wk2WKZ9eHVSvmVgLQ8O3K\')');
        $this->addSql('INSERT INTO dc_user VALUES(3,\'shaun.lizzio@digital.justice.gov.uk\',\'$2y$12$M6AXd9I0x/hdr2rWs31HDOo1frfSJmd9Wk2WKZ9eHVSvmVgLQ8O3K\')');
        $this->addSql('INSERT INTO dc_user VALUES(4,\'robert.ford@digital.justice.gov.uk\',\'$2y$12$M6AXd9I0x/hdr2rWs31HDOo1frfSJmd9Wk2WKZ9eHVSvmVgLQ8O3K\')');
        $this->addSql('INSERT INTO dc_user VALUES(5,\'phil.wilson@digital.justice.gov.uk\',\'$2y$12$M6AXd9I0x/hdr2rWs31HDOo1frfSJmd9Wk2WKZ9eHVSvmVgLQ8O3K\')');
        $this->addSql('INSERT INTO dc_user VALUES(6,\'elizabeth.feenan@digital.justice.gov.uk\',\'$2y$12$M6AXd9I0x/hdr2rWs31HDOo1frfSJmd9Wk2WKZ9eHVSvmVgLQ8O3K\')');
        $this->addSql('INSERT INTO dc_user VALUES(7,\'stephen.petch@digital.justice.gov.uk\',\'$2y$12$M6AXd9I0x/hdr2rWs31HDOo1frfSJmd9Wk2WKZ9eHVSvmVgLQ8O3K\')');
        $this->addSql('ALTER SEQUENCE dc_user_id_seq RESTART WITH 20');

        
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('TRUNCATE TABLE dc_user;');

    }
}
