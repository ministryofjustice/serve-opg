<?php

namespace AppBundle\Service\Security\LoginAttempts;

use AppBundle\Entity\User;
use AppBundle\Service\Security\LoginAttempts\Exception\BruteForceAttackDetectedException;
use Doctrine\ORM\EntityManager;
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
     * @var AttemptsStorage
     */
    private $storage;

    /**
     * @var array
     */
    private $rules;

    public function __construct(EntityManager $em, AttemptsStorage $storage, $rules = [])
    {
        $this->em = $em;
        $this->storage = $storage;
        $this->rules = $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        foreach($this->rules as $rule) {
            list($maxAttempts, $timeRange, $waitFor) = $rule;
            if ($waitFor = $this->storage->hasToWait($username, $maxAttempts, $timeRange, $waitFor, time())) {
                $e = new BruteForceAttackDetectedException($waitFor);
                $e->setHasToWaitForSeconds($waitFor);
                throw $e;
            }
        }

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
        if (empty($this->rules)) {
            return;
        }
        $username = $e->getAuthenticationToken()->getUser();
        if ($username) {
            $this->storage->storeAttempt($username, time());
        }
    }

    /**
     * Reset attempts after a successful login
     *
     * @param AuthenticationEvent $e
     */
    public function onAuthenticationSuccess(AuthenticationEvent $e)
    {
        if (empty($this->rules)) {
            return;
        }

        $user = $e->getAuthenticationToken()->getUser();
        if ($user instanceof User) {
            $this->storage->resetAttempts($user->getEmail());

        }
    }


}