<?php

namespace App\Common;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\StandardSessionConnection;

/**
 * Extension of DynamoDB StandardSessionConnection,
 * that creates the hash table with ID(string) as a key if not existing
 * see more here
 * https://github.com/symfony/symfony/issues/15259
 *
 * Example of usage
```
    framework:
     session:
      name: digideps
      handler_id: dynamo_session_handler

    services:
        Common\SessionConnectionCreatingTable:
        arguments:
            - "@Aws\\DynamoDb\\DynamoDbClient"
            -
                table_name: 'sessions'
                hash_key: 'id'
                max_lock_wait_time: 10
                min_lock_retry_microtime: 500
                max_lock_retry_microtime: 5000

      dynamo_session_handler:
        class: Aws\DynamoDb\SessionHandler
        arguments: [ "@aws_dynamo.session" ]
```
 */
class SessionConnectionCreatingTable extends StandardSessionConnection
{
    /**
     * @var DynamoDbTableCreator
     */
    private $tableCreator;

    public function __construct(DynamoDbClient $client, array $config = [])
    {
        if (empty($config['table_name'])) {
            throw new \InvalidArgumentException(__METHOD__.': table_name missing');
        }
        if (empty($config['hash_key'])) {
            throw new \InvalidArgumentException(__METHOD__.': hash_key missing');
        }
        $this->tableCreator = new DynamoDbTableCreator($client, $config['table_name'], $config['hash_key']);

        parent::__construct($client, $config = []);
    }

    public function read($id)
    {
//        $this->tableCreator->createHashTableIfNotExisting();

        return parent::read($id);
    }

    public function write($id, $data, $isChanged)
    {
//        $this->tableCreator->createHashTableIfNotExisting();

        return parent::write($id, $data, $isChanged);
    }

    public function delete($id)
    {
//        $this->tableCreator->createHashTableIfNotExisting();

        return parent::delete($id);
    }

    public function deleteExpired()
    {
//        $this->tableCreator->createHashTableIfNotExisting();

        parent::deleteExpired();
    }
}
