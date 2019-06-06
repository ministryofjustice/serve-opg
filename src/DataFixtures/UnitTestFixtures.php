<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UnitTestFixtures extends Fixture implements FixtureGroupInterface {

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @var string
     */
    private $fixturesEnabled;

    public function __construct(UserPasswordEncoderInterface $encoder, string $fixturesEnabled)
    {
        $this->encoder = $encoder;
        $this->fixturesEnabled = $fixturesEnabled;
    }

    /**
     * This method must return an array of groups
     * on which the implementing class belongs to
     *
     * @return string[]
     */
    public static function getGroups(): array
    {
        return ['unitTests'];
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        if ($this->fixturesEnabled) {
            $users = [
                ['email' => 'unitTests@digital.justice.gov.uk', 'password' => 'Abcd1234'],
            ];

            foreach ($users as $user) {
                $userModel = new User($user['email']);
                $password = $this->encoder->encodePassword($userModel, $user['password']);
                $userModel->setPassword($password);
                $manager->persist($userModel);
                echo "Added user {$user['email']}\n";
            }
        }

    }
}

