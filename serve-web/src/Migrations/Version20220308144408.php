<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220308144408 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Renaming order_type_id to better name, as it contains the order id, not the type';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE ordertype_deputy RENAME order_type_id TO order_id');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE ordertype_deputy RENAME order_id TO order_type_id');
    }
}
