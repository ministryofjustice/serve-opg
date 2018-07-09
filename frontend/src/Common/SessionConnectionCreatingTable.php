<?php

namespace Common;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\StandardSessionConnection;

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