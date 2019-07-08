<?php declare(strict_types=1);


namespace App\Tests\Helpers;

use App\Entity\User;
use App\Tests\ApiWebTestCase;

class UserTestHelper extends ApiWebTestCase
{

    static public function createUser(string $email, string $password='Abcd1234')
    {
        $userModel = new User($email);
        $encodedPassword = self::getService('security.user_password_encoder.generic')->encodePassword($userModel, $password);
        $userModel->setPassword($encodedPassword);
        return $userModel;
    }
}