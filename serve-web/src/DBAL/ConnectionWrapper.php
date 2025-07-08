<?php

declare(strict_types=1);

namespace App\DBAL;

use Aws\SecretsManager\Exception\SecretsManagerException;
use Aws\SecretsManager\SecretsManagerClient;
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
    private SecretsManagerClient $secretClient;

    public function __construct(
        array $params,
        Driver $driver,
        ?Configuration $config = null,
        ?EventManager $eventManager = null,
    ) {
        parent::__construct($params, $driver, $config, $eventManager);

        $environmentName = getenv(self::ENVIRONMENT_NAME);
        $this->setSecretsManagerClient($environmentName);
        $this->params = $this->getParams();
        $this->autoCommit = $config->getAutoCommit();
    }

    public function connect()
    {
        if (null !== $this->_conn) {
            return false;
        }

        $db_password = getenv(self::DATABASE_PASSWORD);
        // Where password isn't in env var, set one (will be set with real secret when it connects).
        $this->params['password'] = (null == $db_password) ? 'initial_pw' : $db_password;

        try {
            $this->_conn = $this->_driver->connect($this->params);
        } catch (Driver\Exception $e) {
            try {
                $this->refreshPassword();
                $this->_conn = $this->_driver->connect($this->params);
            } catch (Driver\Exception $e) {
                throw $this->convertException($e);
            }
        }

        if (false === $this->autoCommit) {
            $this->beginTransaction();
        }

        $eventManager = $this->getEventManager();

        if ($eventManager->hasListeners(PostConnectEvent::class)) {
            $eventArgs = new PostConnectEventArgs($this);
            $eventManager->dispatchEvent(PostConnectEvent::class, $eventArgs);
        }

        $this->_isConnected = true;

        return true;
    }

    protected function refreshPassword()
    {
        $secretName = 'database-password';

        // Use the Secrets Manager client to retrieve the secret value
        try {
            $result = $this->secretClient->getSecretValue([
                'SecretId' => $secretName,
            ]);
        } catch (SecretsManagerException $e) {
            error_log($e->getMessage());
        }
        // Update params with latest password
        $secretValue = $result['SecretString'] ?? '';
        $this->params['password'] = $secretValue;
    }

    public function setSecretsManagerClient($environmentName)
    {
        if ('local' == $environmentName) {
            $endpoint = 'http://localstack:4566';
            $this->secretClient = new SecretsManagerClient([
                'region' => 'eu-west-1',
                'version' => '2017-10-17',
                'endpoint' => $endpoint,
            ]);
        } else {
            $this->secretClient = new SecretsManagerClient([
                'region' => 'eu-west-1',
                'version' => '2017-10-17',
            ]);
        }
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
