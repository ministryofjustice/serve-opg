<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adds roles column to user - defaults to empty array.
 */
final class Version20190725120206 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // see https://www.doctrine-project.org/projects/doctrine-dbal/en/2.9/reference/types.html#array
        $this->addSql('ALTER TABLE dc_user ADD roles TEXT DEFAULT \'a:0:{}\' NOT NULL');
        $this->addSql('COMMENT ON COLUMN dc_user.roles IS \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dc_user DROP roles');
    }
}
