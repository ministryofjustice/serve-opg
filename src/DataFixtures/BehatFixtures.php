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

class BehatFixtures extends Fixture implements FixtureGroupInterface
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @var bool
     */
    private $fixturesEnabled;

    /**
     * DefaultFixtures constructor.
     * @param UserPasswordEncoderInterface $encoder
     * @param string $fixturesEnabled
     */
    public function __construct(UserPasswordEncoderInterface $encoder, $fixturesEnabled)
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
        return ['behatTests'];
    }

    public function load(ObjectManager $manager)
    {
        if ($this->fixturesEnabled) {
            $users = [
                ['email' => 'elvis.ciotti@digital.justice.gov.uk', 'password' => 'Abcd1234'],
                ['email' => 'sean.privett@digital.justice.gov.uk', 'password' => 'Abcd1234'],
                ['email' => 'shaun.lizzio@digital.justice.gov.uk', 'password' => 'Abcd1234'],
                ['email' => 'robert.ford@digital.justice.gov.uk', 'password' => 'Abcd1234'],
                ['email' => 'test@justice.gov.uk', 'password' => 'password123'],
            ];

            $this->persistUsers($manager, $users);

            $cases = [
                ['number' => '4865226', 'name' => 'Peter Bloggs', 'type' => 'both'],
                ['number' => '88744573', 'name' => 'Victoria Brady', 'type' => 'PF'],
                ['number' => '1267847', 'name' => 'Thomas Jefferson', 'type' => 'HW'],
                ['number' => '9258173', 'name' => 'Susan Grindle', 'type' => 'both'],
                ['number' => '03427488', 'name' => 'Lia Shelton', 'type' => 'PF'],
                ['number' => '51934429', 'name' => 'Abdullah Lang', 'type' => 'PF'],
                ['number' => '6554033', 'name' => 'Angela Walker', 'type' => 'PF'],
                ['number' => '98848454', 'name' => 'Justine Henderson', 'type' => 'PF'],
                ['number' => '99900006', 'name' => 'Justine Henderson', 'type' => 'PF']
            ];

            $this->persistCases($manager, $cases);

            $manager->flush();
        }
    }

    private function persistUsers(ObjectManager $manager, array $users)
    {
        foreach ($users as $user) {
            $userModel = new User($user['email']);
            $password = $this->encoder->encodePassword($userModel, $user['password']);
            $userModel->setPassword($password);
            $manager->persist($userModel);
            echo "Added user {$user['email']}\n";
        }
    }

    private function persistCases(ObjectManager $manager, array $cases)
    {
        foreach ($cases as $case) {
            $client = new Client($case['number'], $case['name'], new DateTime());
            $manager->persist($client);
            echo "Added case {$case['number']}\n";

            if (!$client->hasOrder(Order::TYPE_PF) && ($case['type'] == Order::TYPE_BOTH || $case['type'] == Order::TYPE_PF)) {
                $manager->persist(new OrderPf($client, new DateTime(rand(11, 20).' days ago'), new DateTime(rand(1, 10).' days ago')));
                echo "Added order PF to case {$case['number']}\n";
            }

            if (!$client->hasOrder(Order::TYPE_HW) && ($case['type'] == Order::TYPE_BOTH || $case['type'] == Order::TYPE_HW)) {
                $manager->persist(new OrderHw($client, new DateTime(rand(11, 20).' days ago'), new DateTime(rand(1, 10).' days ago')));
                echo "Added order HW to case {$case['number']}\n";
            }
        }
    }
}
