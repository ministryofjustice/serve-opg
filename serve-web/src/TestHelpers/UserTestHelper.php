<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\User;
use App\Tests\ApiWebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserTestHelper extends ApiWebTestCase
{
    public function createUser(string $email, string $password): User
    {
        $userModel = new User($email);

        /** @var UserPasswordHasherInterface $hasher */
        $hasher = $this->getService('security.password_hasher');

        $userModel->setPassword($hasher->hashPassword($userModel, $password));

        return $userModel;
    }

    public function createAdminUser(string $email, string $password): User
    {
        $userModel = $this->createUser($email, $password);
        $userModel->setRoles(['ROLE_ADMIN']);

        return $userModel;
    }
}
