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
    private function getUserRoles(): array
    {
        return $this->connection->fetchAllAssociative('SELECT id, roles FROM dc_user WHERE roles IS NOT NULL');
    }

    public function getDescription(): string
    {
        return 'Change dc_user.roles from serialized array to JSON';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dc_user ADD roles_json JSON DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN dc_user.roles IS \'json\'');
    }

    public function postUp(Schema $schema): void
    {
        $rows = $this->getUserRoles();

        foreach ($rows as $row) {
            $unserializedData = [];
            if (!is_null($row['roles'])) {
                $unserializedData = @unserialize($row['roles']);
            }

            if (false !== $unserializedData) {
                $jsonData = json_encode($unserializedData);
                $this->connection->executeStatement(
                    'UPDATE dc_user SET roles_json = ? WHERE id = ?',
                    [$jsonData, $row['id']]
                );
            }
        }

        $this->connection->executeStatement('ALTER TABLE dc_user DROP roles');
        $this->connection->executeStatement('ALTER TABLE dc_user RENAME roles_json TO roles');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dc_user ADD roles_array TEXT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN dc_user.roles IS \'array\'');
    }

    public function postDown(Schema $schema): void
    {
        $rows = $this->getUserRoles();

        foreach ($rows as $row) {
            $unserializedData = [];
            if (!is_null($row['roles'])) {
                $unserializedData = json_decode($row['roles']);
            }

            if (!is_null($unserializedData)) {
                $serializedData = serialize($unserializedData);

                $this->connection->executeStatement(
                    'UPDATE dc_user SET roles_array = ? WHERE id = ?',
                    [$serializedData, $row['id']]
                );
            }
        }

        $this->connection->executeStatement('ALTER TABLE dc_user DROP roles');
        $this->connection->executeStatement('ALTER TABLE dc_user RENAME roles_array TO roles');
    }
}
