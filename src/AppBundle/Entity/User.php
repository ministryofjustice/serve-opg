<?php
namespace AppBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

class User implements UserInterface, EquatableInterface
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
     * @var \DateTime|null
     */
    private $activationTokenCreatedAt;

    /**
     * @var string|null
     */
    private $activationToken;

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
        return ['ROLE_USER'];
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
     * @return \DateTime|null
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
        $this->activationTokenCreatedAt = new \DateTime();
    }

    /**
     * Return true if the token is present and create after the TOKEN_EXPIRY value of the constant
     *
     * @return bool
     */
    public function isTokenValid()
    {
        return $this->getActivationTokenCreatedAt()
            && $this->getActivationTokenCreatedAt() >= new \DateTime(self::TOKEN_EXPIRY);
    }

}
