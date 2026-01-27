<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    /**
     * Replace user activation token with a new random one.
     */
    public function refreshActivationToken(User $user): void
    {
        // if the token is still valid
        $newToken = sha1(time().$user->getId().$user->getEmail().rand(17, PHP_INT_MAX));
        $user->setActivationToken($newToken);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function delete(User $user): void
    {
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
    }
}
