<?php

namespace App\Service\Security\LoginAttempts;

use App\Common\BruteForceChecker;
use App\Entity\User;
use App\Service\Security\LoginAttempts\Exception\BruteForceAttackDetectedException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    private EntityManager $em;

    private AttemptsStorageInterface $storage;

    private BruteForceChecker $bruteForceChecker;

    private array $rules;

    public function __construct(EntityManager $em, AttemptsStorageInterface $storage, BruteForceChecker $bruteForceChecker, array $rules = [])
    {
        $this->em = $em;
        $this->storage = $storage;
        $this->bruteForceChecker = $bruteForceChecker;
        $this->rules = $rules;
    }

    /**
     * Return the highest timestamp when the user can be unlocked,
     * based on the rules and previous attempts from the same username, stored in the storage (e.g. dynamoDb).
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

    /**
     * @throws BruteForceAttackDetectedException
     * @throws NotSupported
     */
    public function loadUserByUsername(string $username): User
    {
        if (empty($username)) {
            throw new UserNotFoundException('Missing username');
        }

        if ($this->usernameLockedForSeconds($username)) {
            // throw a generic exception in case of brute force is detected, prior to query the db. The view will query this service re-calling the method and detect if locked
            throw new BruteForceAttackDetectedException();
        }

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $username]);

        if (!$user instanceof User) {
            throw new UserNotFoundException(sprintf('User "%s" not found.', $username));
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
        return User::class === $class || is_subclass_of($class, User::class);
    }

    /**
     * Reset attempts after a successful login.
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

    /**
     * Store failing attempt.
     */
    public function onAuthenticationFailure(AuthenticationEvent $event): void
    {
        if (empty($this->rules)) {
            return;
        }

        $username = $event->getAuthenticationToken()->getCredentials()['email'];
        if ($username) {
            $this->storage->storeAttempt($username, time());
        }
    }

    public function resetUsernameAttempts(string $userId): void
    {
        $this->storage->resetAttempts($userId);

        if ($this->storage->getAttempts($userId)) {
            throw new \Exception("Cannot wipe attempts for $userId");
        }
    }

    /**
     * Upgrades the hashed password of a user, typically for using a better hash algorithm.
     *
     * @throws ORMException
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        $user->setPassword($newHashedPassword);
        $this->em->persist($user);
        $this->em->flush();
    }
}
