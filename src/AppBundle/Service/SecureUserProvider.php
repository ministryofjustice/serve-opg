<?php

namespace AppBundle\Service;


use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SecureUserProvider implements UserProviderInterface
{
    /**
     * @var EntityRepository
     */
    private $repo;

    /**
     * SecureUserProvider constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->repo = $em->getRepository(User::class);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $user = $this->repo->findOneBy(['email' => $username]);

        if ($user instanceof User) {
            return $user;
        }

        throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
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