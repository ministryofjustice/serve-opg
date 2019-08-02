<?php

namespace App\Tests\Migrations;

use App\Tests\ApiWebTestCase;
use App\Tests\Helpers\CommandTestHelper;
use App\Tests\Helpers\UserTestHelper;

class Version20190801154433Test extends ApiWebTestCase
{
    public function testAdminUsersCanLogInPostMigration()
    {
        CommandTestHelper::runMigrations();
        CommandTestHelper::deleteMigrationVersion('20190801154433');

        $user = UserTestHelper::createUser('alex.saunders@digital.justice.gov.uk', 'password123');
        $this->persistEntity($user);

        $dql = <<<DQL
UPDATE App\Entity\User u SET u.roles = 'a:1:{i:0;s:10:\"ROLE_ADMIN\";}' WHERE u.email = 'alex.saunders@digital.justice.gov.uk'
DQL;

        $this->getEntityManager()->createQuery($dql)->execute();

        $client = $this->createClient();
        $client->followRedirects();

        $client->request('GET', '/login', [], []);
        $crawler = $client->submitForm('Sign in', ['_username' => 'alex.saunders@digital.justice.gov.uk', '_password' => 'password123']);
        self::assertStringContainsString('/login', $crawler->getUri());
        self::assertStringContainsString('Authentication request could not be processed due to a system problem.', $crawler->html());

        CommandTestHelper::migrateUpToVersion('20190801154433');

        $client->request('GET', '/login', [], []);
        $crawler = $client->submitForm('Sign in', ['_username' => 'alex.saunders@digital.justice.gov.uk', '_password' => 'password123']);
        self::assertStringContainsString('/case', $crawler->getUri());
    }
}
