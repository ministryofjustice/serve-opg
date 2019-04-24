<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181105102538 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT INTO dc_user VALUES(8,\'farzan.fatemifar@digital.justice.gov.uk\',\'set-me-up\')');
        $this->addSql('ALTER SEQUENCE dc_user_id_seq RESTART WITH 20');


    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM dc_user WHERE id = 8;');

    }
}
