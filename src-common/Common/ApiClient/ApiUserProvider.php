<?php

namespace AppBundle\Service\ApiClient;

use AppBundle\Entity\User;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

/**
 * User provider using API client
 * username=email
 */
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

    /**
     * Called the first time when login
     */
    public function loadUserByUsername($username)
    {
        try {
            return $this->apiClient->loginAndStoreToken( $username);
        } catch (\Exception $e ){
            throw new UsernameNotFoundException($e->getMessage());
        }
    }

    /**
     * Called each time the page is loaded
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        try {
            $user = $this->apiClient->request('GET', '/user/by-id/' . $user->getId().'?from=user-provider', [
                'deserialise_type' => User::class
            ]);
            return $user;
        } catch (\Exception $e ){
            throw new UsernameNotFoundException($e->getMessage());
        }
    }

    public function supportsClass($class)
    {
        return User::class === $class;
    }

}