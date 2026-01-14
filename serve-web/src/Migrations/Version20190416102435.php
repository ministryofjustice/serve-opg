<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190416102435 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("DELETE FROM client WHERE case_number IN ('12637377', '12804927', '12801676', '12588655', '12793003', '12827742', '12827477', '12833148', '12831770', '12842762', '12878811')");
    }

    public function down(Schema $schema): void
    {
    }
}
