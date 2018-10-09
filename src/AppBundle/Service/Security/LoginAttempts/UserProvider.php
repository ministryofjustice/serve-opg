<?php

namespace AppBundle\Service\Security\LoginAttempts;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var array
     */
    private $attemptsConfig;

    /**
     * Checker constructor.
     * @param Storage $storage
     */
    public function __construct(EntityManager $em, Storage $storage, $attemptsConfig = [])
    {
        $this->em = $em;
        $this->storage = $storage;
        $this->attemptsConfig = $attemptsConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        // max attempts check
//        $attempts = $this->storage->getAttempts($username);
//        foreach ($this->attemptsConfig as $range => $wait) {
//            //TODO
//            if (false) {
//                throw new BruteForceAttackDetectedException('');
//            }
//        }

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $username]);

        if (!$user instanceof User) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $refreshedUser = $this->em->getRepository(User::class)->find($user->getId());
        if ($refreshedUser instanceof User) {
            return $refreshedUser;
        }

        throw new UsernameNotFoundException(sprintf('User with id %s not found', $user->getId()));
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === User::class || is_subclass_of($class, User::class);
    }

    /**
     * Store failing attempt
     *
     * @param AuthenticationFailureEvent $e
     */
    public function onAuthenticationFailure(AuthenticationFailureEvent $e)
    {
        if (empty($this->attemptsConfig)) {
            return;
        }
        $username = $e->getAuthenticationToken()->getUser();
        $this->storage->storeAttempt($username, time());
    }

    /**
     * Reset attempts after a successful login
     *
     * @param AuthenticationEvent $e
     */
    public function onAuthenticationSuccess(AuthenticationEvent $e)
    {
        if (empty($this->attemptsConfig)) {
            return;
        }

        $user = $e->getAuthenticationToken()->getUser();
        if ($user instanceof User) {
            $this->storage->resetAttempts($user->getEmail());

        }
    }


}