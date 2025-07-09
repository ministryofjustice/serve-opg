<?php

declare(strict_types=1);

namespace App\DBAL;

use App\Service\PasswordProvider;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;

class ConnectionWrapper extends Connection
{
    public const DATABASE_PASSWORD = 'DC_DB_PASS';
    public const ENVIRONMENT_NAME = 'ENVIRONMENT_NAME';
    private bool $_isConnected = false;

    /**
     * @var array|mixed[]
     */
    private array $params;
    private readonly bool $autoCommit;
    private PasswordProvider $passwordProvider;

    public function __construct(
        array $params,
        Driver $driver,
        ?Configuration $config = null,
        ?EventManager $eventManager = null,
    ) {
        parent::__construct($params, $driver, $config, $eventManager);

        $environmentName = getenv(self::ENVIRONMENT_NAME);
        $this->params = $this->getParams();
        $this->autoCommit = $config->getAutoCommit();
        $this->passwordProvider = new PasswordProvider($environmentName);
    }

    public function connect()
    {
        if (null !== $this->_conn) {
            return false;
        }

        $this->params['password'] = $this->passwordProvider->getEnvPassword(self::DATABASE_PASSWORD);

        try {
            $this->_conn = $this->_driver->connect($this->params);
        } catch (Driver\Exception $e) {
            try {
                $this->params['password'] = $this->passwordProvider->fetchFromSecretsManager();
                $this->_conn = $this->_driver->connect($this->params);
            } catch (Driver\Exception $e) {
                throw $this->convertException($e);
            }
        }

        if (false === $this->autoCommit) {
            $this->beginTransaction();
        }

        $this->_isConnected = true;

        return true;
    }

    public function isConnected()
    {
        return $this->_isConnected;
    }

    public function close()
    {
        if ($this->isConnected()) {
            parent::close();
            $this->_isConnected = false;
        }
    }
}
