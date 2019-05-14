<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    /**
     * Replace user activation token with a new random one
     *
     * @param User $user
     */
    public function refreshActivationToken(User $user)
    {
        // if the token is still valid->
        $newToken = sha1(time(true) . $user->getId() . $user->getEmail() . rand(17, PHP_INT_MAX));
        $user->setActivationToken($newToken);
        $this->_em->flush($user);
    }

}
