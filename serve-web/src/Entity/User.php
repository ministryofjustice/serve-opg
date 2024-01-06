<?php
namespace App\Entity;

use DateTime;
use Serializable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="dc_user")
 * @ORM\HasLifecycleCallbacks
 */
class User implements UserInterface, EquatableInterface
{
    /**
     * @var string
     */
    const TOKEN_EXPIRY = '48 hours ago';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     */
    private ?string $email;

    /**
     * @ORM\Column(name="password", type="string", length=255)
     */
    private string $password;

    /**
     * @ORM\Column(name="activation_token_created_at", type="datetime", nullable=true)
     */
    private ?DateTime $activationTokenCreatedAt = null;

    /**
     * @ORM\Column(name="activation_token", type="string", length=40, nullable=true)
     */
    private ?string $activationToken = null;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private ?DateTime $createdAt = null;

    /**
     * @ORM\Column(name="last_login_at", type="datetime", nullable=true)
     */
    private ?DateTime $lastLoginAt = null;

    /**
     * @ORM\Column(name="roles", type="array")
     */
    private array $roles = [];

    /**
     * @ORM\Column(name="first_name", type="string", length=100, nullable=true)
     */
    private ?string $firstName = null;

    /**
     * @ORM\Column(name="last_name", type="string", length=100, nullable=true)
     */
    private ?string $lastName = null;

    /**
     * @ORM\Column(name="phone_number", type="string", length=20, nullable=true)
     */
    private ?string $phoneNumber = null;

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
    public function setId($id): void
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

    /**
     * @param string
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    public function getRoles()
    {
        return array_unique(array_merge(['ROLE_USER'], $this->roles));
    }

    public function isAdmin()
    {
        return in_array('ROLE_ADMIN', $this->roles);
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @param string $password
     */
    public function setPassword($password): void
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

    public function eraseCredentials(): void
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
    public function setActivationToken($activationToken): void
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

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName) ?: $this->email;
    }

    /**
     * @return string|null
     */
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber(string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist(): void
    {
        $this->setCreatedAt(new DateTime());
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'password' => $this->password,
        ];
    }

    /** @see \Serializable::unserialize() */
    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->email = $data['email'];
        $this->password = $data['password'];
    }
}
