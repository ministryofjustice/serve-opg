<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    /**
     * @param string $email
     *
     * @return User|false
     */
    public function findOneByEmail($email)
    {
        return $this->findOneBy(['email' => $email]);
    }

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
     * Recreate random token if empty or older than elf::REFRESH_TOKEN_IF_OLDER_THAN
     *
     * @param User $user
     * @return bool true if changed, false if not
     */
    public function refreshActivationTokenIfNeeded(User $user)
    {
        // if the token is still valid->
        if (!$user->isTokenValid()) {
            $newToken = sha1(time(true) . $user->getId() . $user->getEmail() . rand(17, PHP_INT_MAX));
            $user->setActivationToken($newToken);
            $this->_em->flush($user);

            return true;
        }

        return false;
    }

}
