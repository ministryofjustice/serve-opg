<?php
namespace App\Entity;

use DateTime;
use Serializable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Table(name: 'dc_user')]
#[ORM\Entity(repositoryClass: 'App\Repository\UserRepository')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, EquatableInterface,PasswordAuthenticatedUserInterface
{
    /**
     * @var string
     */
    const TOKEN_EXPIRY = '48 hours ago';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(name: 'email', type: 'string', length: 255, unique: true)]
    private ?string $email;

    #[ORM\Column(name: 'password', type: 'string', length: 255)]
    private string $password;

    #[ORM\Column(name: 'activation_token_created_at', type: 'datetime', nullable: true)]
    private ?DateTime $activationTokenCreatedAt = null;

    #[ORM\Column(name: 'activation_token', type: 'string', length: 40, nullable: true)]
    private ?string $activationToken = null;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
    private ?DateTime $createdAt = null;

    #[ORM\Column(name: 'last_login_at', type: 'datetime', nullable: true)]
    private ?DateTime $lastLoginAt = null;

    #[ORM\Column(name: 'roles', type: 'array')]
    private array $roles = [];

    #[ORM\Column(name: 'first_name', type: 'string', length: 100, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(name: 'last_name', type: 'string', length: 100, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(name: 'phone_number', type: 'string', length: 20, nullable: true)]
    private ?string $phoneNumber = null;

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getRoles(): array
    {
        return array_unique(array_merge(['ROLE_USER'], $this->roles));
    }

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->roles);
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getSalt(): string
    {
        return '';
    }

    public function getUsername(): ?string
    {
        return $this->email;
    }

    public function isEqualTo(UserInterface $user): bool
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

    public function getActivationTokenCreatedAt(): ?DateTime
    {
        return $this->activationTokenCreatedAt;
    }

    public function getActivationToken(): ?string
    {
        return $this->activationToken;
    }

    public function setActivationToken(?string $activationToken): void
    {
        $this->activationToken = $activationToken;
        $this->activationTokenCreatedAt = new DateTime();
    }

    /**
     * Return true if the token is present and create after the TOKEN_EXPIRY value of the constant.
     */
    public function isTokenValid(): bool
    {
        return $this->getActivationTokenCreatedAt()
            && $this->getActivationTokenCreatedAt() >= new DateTime(self::TOKEN_EXPIRY);
    }

    public function getLastLoginAt(): ?DateTime
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?DateTime $lastLoginAt): void
    {
        $this->lastLoginAt = $lastLoginAt;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName) ?: $this->email;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    #[ORM\PrePersist]
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
