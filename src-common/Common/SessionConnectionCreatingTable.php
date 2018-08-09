<?php

namespace Common;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\StandardSessionConnection;

/**
 * Extension of DynamoDB StandardSessionConnection,
 * that creates the hash table with ID(string) as a key if not existing
 *
 * Example of usage
```
    framework:
     session:
      name: digideps
      handler_id: dynamo_session_handler

    services:
     aws_dynamo.session:
        class: Common\SessionConnectionCreatingTable
        arguments:
            - "@aws_dynamo.client"
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
    public function __construct(DynamoDbClient $client, array $config = [])
    {
        $tableName = $config['table_name'];

        parent::__construct($client, $config = []);

        // enable the following to delete the table, for testing purposes only
        //$client->deleteTable(['TableName' => $tableName]);

        // create table on the fly if not existing
        if (!in_array($tableName, $client->listTables()['TableNames'])) {
            $params = [
                'TableName' => $tableName,
                'KeySchema' => [
                    [
                        'AttributeName' => 'id',
                        'KeyType' => 'HASH',  //Partition key
                    ]
                ],
                'AttributeDefinitions' => [
                    [
                        'AttributeName' => 'id',
                        'AttributeType' => 'S'
                    ]
                ],
                'ProvisionedThroughput' => [
                    'ReadCapacityUnits' => 10,
                    'WriteCapacityUnits' => 10
                ]
            ];
            $client->createTable($params);
        }
    }
}
