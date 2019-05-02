<?php

namespace App\Common;

use Aws\DynamoDb\DynamoDbClient;

/**
 * Creates a dynamodb hash table, given the tableName and the key to use as Id
 */
class DynamoDbTableCreator
{
    /**
     * @var DynamoDbClient
     */
    private $client;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $keyAttrName;

    /**
     * @var boolean array cache to avoid querying dynamo in the same script lifespan
     */
    private static $tableCreated = null;

    /**
     * DynamoDbTableCreator constructor.
     * @param DynamoDbClient $client
     * @param string $tableName
     * @param string $keyAttrName
     */
    public function __construct(DynamoDbClient $client, string $tableName, string $keyAttrName)
    {
        $this->client = $client;
        $this->tableName = $tableName;
        $this->keyAttrName = $keyAttrName;
    }

    public function createHashTableIfNotExisting()
    {
        // enable the following to delete the table, for testing purposes only
        //$this->client->deleteTable(['TableName' => $tableName]);

        if (true === self::$tableCreated) {
            return;
        }

//        aws dynamodb create-table
//            --table-name sessions
//            --attribute-definitions AttributeName=sessions,AttributeType=S
//            --key-schema AttributeName=id,KeyType=HASH
//            --provisioned-throughput ReadCapacityUnits=10,WriteCapacityUnits=10

        // create table on the fly if not existing
        if (!in_array($this->tableName, $this->client->listTables()['TableNames'])) {
            $params = [
                'TableName' => $this->tableName,
                'KeySchema' => [
                    [
                        'AttributeName' => $this->keyAttrName,
                        'KeyType' => 'HASH',  //Partition key
                    ]
                ],
                'AttributeDefinitions' => [
                    [
                        'AttributeName' => $this->keyAttrName,
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

        // At this point, the table is either already created, or just created.
        // The static variable is set to avoid unnecessary subsequent "listTable" queries
        self::$tableCreated = true;
    }
}
