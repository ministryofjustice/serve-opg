<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191217150640 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $now = date("Y-m-d h:m:s");

        $this->addSql("INSERT INTO dc_user (id, email, password, created_at, roles) VALUES(nextval('dc_user_id_seq'), 'al-amin.vintura@justice.gov.uk', 'set-me-up', '${now}', 'a:0:{}')");

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql("DELETE FROM dc_user WHERE email = 'al-amin.vintura@justice.gov.uk';");
    }
}
