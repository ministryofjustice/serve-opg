<?php declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\ApiWebTestCase;
use App\TestHelpers\UserTestHelper;
use DateTime;

class UserTest extends ApiWebTestCase
{
    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testCreatedAtIsAddedOnPersist()
    {
        $user = UserTestHelper::createUser('atest@user.com');
        self::assertNull($user->getCreatedAt());

        ApiWebTestCase::getService('doctrine')->getManager()->persist($user);
        ApiWebTestCase::getService('doctrine')->getManager()->flush();

        self::assertInstanceOf(DateTime::class, $user->getCreatedAt());
    }

    public function testSettingRolesRetainsRoleUser()
    {
        $user = UserTestHelper::createUser('atest@user.com');
        $user->setRoles(['SOME_ROLE_HERE', 'SOME_OTHER_ROLE']);

        foreach( ['ROLE_USER', 'SOME_ROLE_HERE', 'SOME_OTHER_ROLE'] as $role) {
            self::assertContains($role, $user->getRoles());
        }
    }
}