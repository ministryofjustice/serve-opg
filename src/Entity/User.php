<?php
namespace App\Entity;

use DateTime;
use Serializable;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

class User implements UserInterface, EquatableInterface, Serializable
{
    /**
     * @var string
     */
    const TOKEN_EXPIRY = '48 hours ago';

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $password;

    /**
     * @var DateTime|null
     */
    private $activationTokenCreatedAt;

    /**
     * @var string|null
     */
    private $activationToken;

    /**
     * @var DateTime|null
     */
    private $createdAt;

    /**
     * @var DateTime|null
     */
    private $lastLoginAt;

    /**
     * @var array
     */
    private $roles = [];

    /**
     * User constructor.
     * @param string $email
     */
    public function __construct(string $email)
    {
        $this->email = $email;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    public function getRoles()
    {
        return array_unique(array_merge(['ROLE_USER'], $this->roles));
    }

    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return '';
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof User) {
            return false;
        }

        if ($this->email !== $user->getEmail()) {
            return false;
        }

        return true;
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * @return DateTime|null
     */
    public function getActivationTokenCreatedAt()
    {
        return $this->activationTokenCreatedAt;
    }

    /**
     * @return string
     */
    public function getActivationToken()
    {
        return $this->activationToken;
    }

    /**
     * @param string|null $activationToken
     */
    public function setActivationToken($activationToken)
    {
        $this->activationToken = $activationToken;
        $this->activationTokenCreatedAt = new DateTime();
    }

    /**
     * Return true if the token is present and create after the TOKEN_EXPIRY value of the constant
     *
     * @return bool
     */
    public function isTokenValid()
    {
        return $this->getActivationTokenCreatedAt()
            && $this->getActivationTokenCreatedAt() >= new DateTime(self::TOKEN_EXPIRY);
    }

    /**
     * @return DateTime|null
     */
    public function getLastLoginAt(): ?DateTime
    {
        return $this->lastLoginAt;
    }

    /**
     * @param DateTime|null $lastLoginAt
     */
    public function setLastLoginAt(?DateTime $lastLoginAt): void
    {
        $this->lastLoginAt = $lastLoginAt;
    }

    /**
     * @return DateTime|null
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime|null $createdAt
     */
    public function setCreatedAt(?DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function onPrePersist()
    {
        $this->setCreatedAt(new DateTime());
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->email,
            $this->password,
            // see section on salt below
            // $this->salt,
        ]);
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        [
            $this->id,
            $this->email,
            $this->password,
        ] = unserialize($serialized, ['allowed_classes' => false]);
    }
}
