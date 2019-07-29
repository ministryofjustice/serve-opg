<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Throwable;

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

    /**
     * @param User $user
     * @return void|Throwable
     */
    public function delete(User $user)
    {
        try{
            $this->getEntityManager()->remove($user);
            $this->getEntityManager()->flush();
        } catch(Throwable $e) {
            return $e;
        }
    }

}
