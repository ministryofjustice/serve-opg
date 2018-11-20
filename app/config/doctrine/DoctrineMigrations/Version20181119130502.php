<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181119130502 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT INTO dc_user VALUES(9,\'gulled.hersi@justice.gov.uk\',\'set-me-up\')');
        $this->addSql('INSERT INTO dc_user VALUES(10,\'jennifer.alleyne@justice.gov.uk\',\'set-me-up\')');
        $this->addSql('INSERT INTO dc_user VALUES(11,\'sami.idris@justice.gov.uk\',\'set-me-up\')');
        $this->addSql('INSERT INTO dc_user VALUES(12,\'kayne.hamilton@justice.gov.uk\',\'set-me-up\')');
        $this->addSql('ALTER SEQUENCE dc_user_id_seq RESTART WITH 50');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM dc_user WHERE id = 8;');
        $this->addSql('DELETE FROM dc_user WHERE id = 9;');
        $this->addSql('DELETE FROM dc_user WHERE id = 10;');
        $this->addSql('DELETE FROM dc_user WHERE id = 11;');

    }
}
