<?php

namespace App\Service\Security\LoginAttempts;

use App\Entity\User;
use App\Service\Security\LoginAttempts\Exception\BruteForceAttackDetectedException;
use App\Common\BruteForceChecker;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var AttemptsStorageInterface
     */
    private $storage;

    /**
     * @var BruteForceChecker
     */
    private $bruteForceChecker;

    /**
     * @var array
     */
    private $rules;

    public function __construct(EntityManager $em, AttemptsStorageInterface $storage, BruteForceChecker $bruteForceChecker, $rules = [])
    {
        $this->em = $em;
        $this->storage = $storage;
        $this->bruteForceChecker = $bruteForceChecker;
        $this->rules = $rules;
    }

    /**
     * Return the highest timestamp when the user can be unlocked,
     * based on the rules and previous attempts from the same username, stored in the storage (e.g. dynamoDb)
     *
     * Return false,if *
     *
     * @param $username
     *
     * @return bool|int
     */
    public function usernameLockedForSeconds($username)
    {
        $waits = [];
        foreach ($this->rules as $rule) {
            // fetch all the rules and check if for any of those, the user has to wait
            list($maxAttempts, $timeRange, $waitFor) = $rule;
            if ($waitFor = $this->bruteForceChecker->hasToWait($this->storage->getAttempts($username), $maxAttempts, $timeRange, $waitFor, time())) {
                $waits[] = $waitFor;
            }
        }

        return $waits ? max($waits) : false;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        if (empty($username)) {
            throw new UsernameNotFoundException('Missing username');
        }

        if ($this->usernameLockedForSeconds($username)) {
            // throw a generic exception in case of brute force is detected, prior to query the db. The view will query this service re-calling the method and detect if locked
            throw new BruteForceAttackDetectedException();
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
     * @param AuthenticationFailureEvent $event
     */
    public function onAuthenticationFailure(AuthenticationFailureEvent $event)
    {
        if (empty($this->rules)) {
            return;
        }

        $username = $event->getAuthenticationToken()->getCredentials()['email'];
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
            $this->resetUsernameAttempts($user->getEmail());
        }
    }

    public function resetUsernameAttempts($userId)
    {
        $this->storage->resetAttempts($userId);

        if ($this->storage->getAttempts($userId)) {
            throw new \Exception("Cannot wipe attempts for $userId");
        }
    }
}
