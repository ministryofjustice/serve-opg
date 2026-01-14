<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260108175816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dc_user ADD roles_json JSON DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN dc_user.roles IS \'json\'');
    }

    public function postUp(Schema $schema): void
    {
        $connection = $this->connection;
        $rows = $connection->fetchAllAssociative('SELECT id, roles FROM dc_user WHERE roles IS NOT NULL');

        foreach ($rows as $row) {
            $unserializedData = @unserialize($row['roles']);

            if (false !== $unserializedData) {
                $jsonData = json_encode($unserializedData);
                $connection->executeStatement(
                    'UPDATE dc_user SET roles_json = ? WHERE id = ?',
                    [$jsonData, $row['id']]
                );
            }
        }

        $connection->executeStatement('ALTER TABLE dc_user DROP roles');
        $connection->executeStatement('ALTER TABLE dc_user RENAME roles_json TO roles');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dc_user ALTER roles TYPE TEXT');
        $this->addSql('COMMENT ON COLUMN dc_user.roles IS \'(DC2Type:array)\'');
    }
}
