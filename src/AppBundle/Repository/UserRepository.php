<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    /**
     * @param string $email
     *
     * @return User|false User or false if not found, or token is expired
     */
    public function findOneByValidToken($token)
    {
        $user = $this->findOneBy(['activationToken' => $token]);

        return $user instanceof User && $user->isTokenValid() ? $user : false;
    }

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
