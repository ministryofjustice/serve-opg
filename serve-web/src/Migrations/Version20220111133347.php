<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220111133347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Dropping the unique index on order_number to account for rare occasions where we get a duplicate order number';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_450EF9F7551F0F81');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX UNIQ_450EF9F7551F0F81 ON dc_order (order_number)');
    }
}
