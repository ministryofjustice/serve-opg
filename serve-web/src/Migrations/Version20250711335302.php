<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250710153000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates readonly IAM user with rds_iam for Aurora PostgreSQL';
    }

    public function up(Schema $schema): void
    {
        $env = getenv('WORKSPACE');
        $username = "readonly-db-iam-$env";

        $this->addSql(<<<SQL
DO
\$\$
BEGIN

  IF NOT EXISTS (SELECT * FROM pg_user WHERE usename = '$username') THEN
     CREATE USER "$username" WITH LOGIN;
  END IF;

  IF EXISTS (SELECT * FROM pg_roles WHERE rolname = 'rds_iam') THEN
     GRANT rds_iam TO "$username";
  END IF;

  IF EXISTS (SELECT * FROM pg_roles WHERE rolname = 'pg_read_all_data') THEN
     GRANT pg_read_all_data TO "$username";
  END IF;

  ALTER USER "$username" SET log_statement = 'all';

END
\$\$;
SQL);
    }
}
