<?php declare(strict_types=1);

namespace App\Tests\Migrations;

use App\Tests\BaseFunctionalTestCase;
use App\TestHelpers\CommandTestHelper;
use App\TestHelpers\FixtureTestHelper;

class Version20190801154433Test extends BaseFunctionalTestCase
{
    /**
     * @var FixtureTestHelper
     */
    private $fixtureHelper;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private $client;

    public function setUp(): void
    {
        parent::setUp();
        $this->fixtureHelper = $this->getService('App\TestHelpers\FixtureTestHelper');
        $this->client = $this->createClient();
        $this->client->followRedirects();
    }

    public function testAdminUsersCanLogInPostMigration()
    {
        CommandTestHelper::runMigrations();
        CommandTestHelper::deleteMigrationVersion('20190801154433');

        $this->fixtureHelper->loadUserFixture('adminUsers.yaml');

        $dql = <<<DQL
UPDATE App\Entity\User u SET u.roles = 'a:1:{i:0;s:10:\"ROLE_ADMIN\";}' WHERE u.email = 'alex.saunders@digital.justice.gov.uk'
DQL;

        $this->getEntityManager()->createQuery($dql)->execute();

        $this->client->request('GET', '/login', [], []);
        $crawler = $this->client->submitForm('Sign in', ['_username' => 'alex.saunders@digital.justice.gov.uk', '_password' => 'Abcd1234']);
        self::assertStringContainsString('/login', $crawler->getUri());
        self::assertStringContainsString('Authentication request could not be processed due to a system problem.', $crawler->html());

        CommandTestHelper::migrateUpToVersion('20190801154433');

        $this->client->request('GET', '/login', [], []);
        $crawler = $this->client->submitForm('Sign in', ['_username' => 'alex.saunders@digital.justice.gov.uk', '_password' => 'Abcd1234']);
        self::assertStringContainsString('/case', $crawler->getUri());
    }
}
