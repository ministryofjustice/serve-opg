<?php

namespace AppBundle\Command;

use Aws\DynamoDb\DynamoDbClient;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SetupCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dc:setup')
            ->setDescription('Check or print validation rules')
            ->addOption('print', null, InputOption::VALUE_NONE, 'print validation rules')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dynamoDb = $this->getContainer()->get('aws_dynamo.client'); /* @var $dynamoDb DynamoDbClient*/

        //use https://docs.aws.amazon.com/amazondynamodb/latest/developerguide/GettingStarted.PHP.html
        // create simple table for
        $params = [
            'TableName' => 'sessions',
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


        $dynamoDb->createTable($params);
    }
}
