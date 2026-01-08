<?php

declare(strict_types=1);

namespace App\DBAL;

use App\Service\PasswordProvider;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Connection as DriverConnection;

class ConnectionWrapper extends Connection
{
    public const string DATABASE_PASSWORD = 'DC_DB_PASS';
    public const string ENVIRONMENT_NAME = 'ENVIRONMENT_NAME';
    private bool $_isConnected = false;
    private array $params;
    private readonly bool $autoCommit;
    private PasswordProvider $passwordProvider;

    public function __construct(
        array $params,
        Driver $driver,
        ?Configuration $config = null,
    ) {
        parent::__construct($params, $driver, $config);

        $environmentName = getenv(self::ENVIRONMENT_NAME);
        if (!is_string($environmentName)) {
            $environmentName = 'local';
        }

        $this->params = $this->getParams();
        $this->autoCommit = $config->getAutoCommit();
        $this->passwordProvider = new PasswordProvider($environmentName);
    }

    public function connect(): DriverConnection
    {
        if (null !== $this->_conn) {
            return $this->_conn;
        }

        $this->params['password'] = $this->passwordProvider->getEnvPassword(self::DATABASE_PASSWORD);

        try {
            $this->_conn = $this->driver->connect($this->params);
        } catch (Driver\Exception) {
            try {
                $this->params['password'] = $this->passwordProvider->fetchFromSecretsManager();
                $this->_conn = $this->driver->connect($this->params);
            } catch (Driver\Exception $e) {
                throw $this->convertException($e);
            }
        }

        if (false === $this->autoCommit) {
            $this->beginTransaction();
        }

        $this->_isConnected = true;

        return $this->_conn;
    }

    public function isConnected(): bool
    {
        return $this->_isConnected;
    }

    public function close(): void
    {
        if ($this->isConnected()) {
            parent::close();
            $this->_isConnected = false;
        }
    }
}
