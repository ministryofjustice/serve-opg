<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\User;
use App\Tests\ApiWebTestCase;

class UserTestHelper extends ApiWebTestCase
{
    public function createUser(string $email, string $password): User
    {
        $userModel = new User($email);
        $encodedPassword = $this->getService('security.user_password_encoder.generic')->encodePassword($userModel, $password);
        $userModel->setPassword($encodedPassword);

        return $userModel;
    }

    public function createAdminUser(string $email, string $password): User
    {
        $userModel = $this->createUser($email, $password);
        $userModel->setRoles(['ROLE_ADMIN']);

        return $userModel;
    }
}
