<?php

namespace App\Tests\Migrations;

use App\Entity\User;
use App\Tests\ApiWebTestCase;
use App\Tests\Helpers\UserTestHelper;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class Version20190801154433Test extends ApiWebTestCase
{
    public function testAdminUsersCanLogInPostMigration()
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $command = $application->find('doctrine:migrations:version');
        $commandTester = new CommandTester($command);
        // Answer yes to migration - --no-interaction doesn't work for CommandTester
        $commandTester->setInputs(['y']);
        $commandTester->execute([
            'command'  => $command->getName(),
            '--delete' => ' ',
            'version' => '20190801154433',
        ]);

        $user = UserTestHelper::createUser('alex.saunders@digital.justice.gov.uk', 'password123');
        $this->persistEntity($user);

        $dql = <<<DQL
UPDATE App\Entity\User u SET u.roles = 'a:1:{i:0;s:10:\"ROLE_ADMIN\";}' WHERE u.email = 'alex.saunders@digital.justice.gov.uk'
DQL;

        $this->getEntityManager()->createQuery($dql)->execute();

        $client = $this->createClient();
        $client->followRedirects();

        /** @var User $user */
        $client->request('GET', '/login', [], []);
        $crawler = $client->submitForm('Sign in', ['_username' => 'alex.saunders@digital.justice.gov.uk', '_password' => 'password123']);
        self::assertStringContainsString('/login', $crawler->getUri());

        $command = $application->find('doctrine:migrations:execute');
        $commandTester = new CommandTester($command);
        // Answer yes to migration - --no-interaction doesn't work for CommandTester
        $commandTester->setInputs(['y']);
        $commandTester->execute([
            'command'  => $command->getName(),
            'version' => '20190801154433',
            '--up' => '',
        ]);

        $client->request('GET', '/login', [], []);
        $crawler = $client->submitForm('Sign in', ['_username' => 'alex.saunders@digital.justice.gov.uk', '_password' => 'password123']);
        self::assertStringContainsString('/case', $crawler->getUri());
    }
}
