<?php declare(strict_types=1);

namespace App\DataFixtures;


use App\TestHelpers\FixtureTestHelper;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Persistence\ObjectManager;

class BehatFixtures extends Fixture implements FixtureGroupInterface
{
    /**
     * @var bool
     */
    private $fixturesEnabled;

    /**
     * @var FixtureTestHelper
     */
    private $fixtureHelper;

    /**
     * DefaultFixtures constructor.
     * @param string $fixturesEnabled
     * @param FixtureTestHelper $fixtureHelper
     */
    public function __construct($fixturesEnabled, FixtureTestHelper $fixtureHelper)
    {
        $this->fixturesEnabled = $fixturesEnabled;
        $this->fixtureHelper = $fixtureHelper;
    }

    /**
     * This method must return an array of groups
     * on which the implementing class belongs to
     *
     * @return string[]
     */
    public static function getGroups(): array
    {
        return ['behatTests'];
    }

    public function load(ObjectManager $manager)
    {
        if ($this->fixturesEnabled) {
            $this->fixtureHelper->loadUserFixture('adminUsers.yaml');
            $this->fixtureHelper->loadUserFixture('testUsers.yaml');
            $this->fixtureHelper->loadCaseFixture('cases.yaml');
        }
    }
}
