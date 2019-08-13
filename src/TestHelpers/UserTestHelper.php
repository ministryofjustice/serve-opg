<?php declare(strict_types=1);


namespace App\TestHelpers;

use App\Entity\User;
use App\Tests\BaseFunctionalTestCase;

class UserTestHelper extends BaseFunctionalTestCase
{

    static public function createUser(string $email, string $password='Abcd1234')
    {
        $userModel = new User($email);
        $encodedPassword = BaseFunctionalTestCase::getService('security.user_password_encoder.generic')->encodePassword($userModel, $password);
        $userModel->setPassword($encodedPassword);
        return $userModel;
    }

    static public function createAdminUser(string $email, string $password='Abcd1234')
    {
        $userModel = self::createUser($email, $password);
        $userModel->setRoles(['ROLE_ADMIN']);
        return $userModel;
    }
}
