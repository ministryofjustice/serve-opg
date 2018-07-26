<?php

namespace AppBundle\Service\ApiClient;

use AppBundle\Entity\User;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;


class ApiUserProvider implements UserProviderInterface
{
    /**
     * @var Client
     */
    private $apiClient;

    /**
     * UserProvider constructor.
     * @param Client $apiClient
     */
    public function __construct(Client $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function loadUserByUsername($username)
    {
        return $this->fetchUser($username);
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        $username = $user->getUsername();

        return $this->fetchUser($username);
    }

    public function supportsClass($class)
    {
        return User::class === $class;
    }

    private function fetchUser($username)
    {
        $user = new User($username);
        return $user;

//        $ret = $this->apiClient->request('GET', '/user/by-email/' . $username);
//        echo "<pre>"; \Doctrine\Common\Util\Debug::dump($ret);die;




        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)
        );
    }


}