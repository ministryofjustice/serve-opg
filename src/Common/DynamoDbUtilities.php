<?php

namespace Common;

use Aws\DynamoDb\DynamoDbClient;

class DynamoDbUtilities
{
    /**
     * @var DynamoDbClient
     */
    private $client;

    /**
     * DynamoDbTableCreator constructor.
     * @param DynamoDbClient $this->client
     */
    public function __construct(DynamoDbClient $client)
    {
        $this->client = $client;
    }

    public function createHashTableIfNotExisting($tableName, $keyAttrName)
    {
        // enable the following to delete the table, for testing purposes only
        //$this->client->deleteTable(['TableName' => $tableName]);

        // create table on the fly if not existing
        if (!in_array($tableName, $this->client->listTables()['TableNames'])) {
            $params = [
                'TableName' => $tableName,
                'KeySchema' => [
                    [
                        'AttributeName' => $keyAttrName,
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
            $this->client->createTable($params);
        }
    }

}