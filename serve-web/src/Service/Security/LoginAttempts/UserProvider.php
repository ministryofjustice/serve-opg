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
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    private EntityManager $em;

    private AttemptsStorageInterface $storage;

    private BruteForceChecker $bruteForceChecker;

    private array $rules;

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
     */
    public function usernameLockedForSeconds(string $username): bool|int
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

    public function loadUserByIdentifier($identifier): User
    {
        if (empty($identifier)) {
            throw new UserNotFoundException('Missing username');
        }

        if ($this->usernameLockedForSeconds($identifier)) {
            // throw a generic exception in case of brute force is detected, prior to query the db. The view will query this service re-calling the method and detect if locked
            throw new BruteForceAttackDetectedException();
        }

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $identifier]);

        if (!$user instanceof User) {
            throw new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): User
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $refreshedUser = $this->em->getRepository(User::class)->find($user->getId());
        if ($refreshedUser instanceof User) {
            return $refreshedUser;
        }

        throw new UserNotFoundException(sprintf('User with id %s not found', $user->getId()));
    }

    public function supportsClass($class): bool
    {
        return $class === User::class || is_subclass_of($class, User::class);
    }

    /**
     * Store failing attempt
     *
     * @param AuthenticationFailureEvent|AuthenticationEvent $event
     *
     * TO DO 2024
     * Should just be AuthenticationFailureEvent but we can't mock this deprecated Final class in testing
     * This will come out entirely when we switch to Symfony's newer authentication system
     */
    public function onAuthenticationFailure(AuthenticationFailureEvent|AuthenticationEvent $event): void
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
     */
    public function onAuthenticationSuccess(AuthenticationEvent $e): void
    {
        if (empty($this->rules)) {
            return;
        }

        $user = $e->getAuthenticationToken()->getUser();
        if ($user instanceof User) {
            $this->resetUsernameAttempts($user->getEmail());
        }
    }

    public function resetUsernameAttempts(string $userId): void
    {
        $this->storage->resetAttempts($userId);

        if ($this->storage->getAttempts($userId)) {
            throw new \Exception("Cannot wipe attempts for $userId");
        }
    }

    public function loadUserByUsername(string $username) : void
    {
        // TODO: Implement loadUserByUsername() method.
    }
}
