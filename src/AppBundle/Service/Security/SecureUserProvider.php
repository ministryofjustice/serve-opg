<?php

namespace AppBundle\Service\Security;


use AppBundle\Entity\User;
use AppBundle\Service\Security\LoginAttempts\Checker as LoginAttemptsChecker;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

// replace with https://symfony.com/doc/3.4/security/custom_authentication_provider.html
class SecureUserProvider implements UserProviderInterface
{
    /**
     * @var EntityRepository
     */
    private $repo;

    /**
     * @var LoginAttemptsChecker
     */
    private $loginAttemptsChecker;

    public function __construct(EntityManager $em, LoginAttemptsChecker $loginAttemptsChecker)
    {
        $this->repo = $em->getRepository(User::class);
        $this->loginAttemptsChecker = $loginAttemptsChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $user = $this->repo->findOneBy(['email' => $username]);

        if (!$user instanceof User) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        }

        if ($this->loginAttemptsChecker->isUserLocked($username)){
            throw new \Exception('locked');
        }
        $this->loginAttemptsChecker->registerUserLoginFailure($username, time());

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

        $refreshedUser = $this->repo->find($user->getId());
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

}