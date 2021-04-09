<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Order;
use App\Entity\OrderHw;
use App\Entity\OrderPf;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class DefaultFixtures extends Fixture
{
    /**
     * @var string
     */
    private $fixturesEnabled;

    public function __construct($fixturesEnabled)
    {

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
        // TODO: Implement getGroups() method.
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        // TODO: Implement load() method.
    }
}
