<?php

namespace AppBundle\DataFixtures;

use AppBundle\Entity\Client;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderHw;
use AppBundle\Entity\OrderPa;
use AppBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class DefaultFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * DefaultFixtures constructor.
     * @param PasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }


    public function load(ObjectManager $manager)
    {
        // users
        $repo = $manager->getRepository(User::class);
        $userLines = array_filter(explode("\n", getenv('DC_FIXURES_USERS')));
        foreach ($userLines as $userLine) {
            parse_str($userLine, $user);
            if (!empty($user['email']) && !$repo->findOneBy(['email' => $user['email']])) {
                $u = new User($user['email']);
                $pass = $this->encoder->encodePassword($u, $user['password']);
                $u->setPassword($pass);
                $manager->persist($u);
                echo "Added user {$user['email']}\n";
            }
        }

        // clients
        $repo = $manager->getRepository(Client::class);
        $caseLines = array_filter(explode("\n", getenv('DC_FIXURES_CASES')));
        foreach ($caseLines as $caseLine) {
            parse_str($caseLine, $case);
            if (!$client = $repo->findOneBy(['caseNumber' => $case['number']])) {
                $client = new Client($case['number'], $case['name'], new \DateTime());
                $manager->persist($client);
                echo "Added case {$case['number']}\n";
            }

            if (!$client->hasOrder( Order::TYPE_PA) && ($case['type'] == Order::TYPE_BOTH || $case['type'] == Order::TYPE_PA)) {
                $manager->persist(new OrderPa($client, new \DateTime(rand(1, 10).' days ago')));
                echo "Added order PA to case {$case['number']}\n";
            }
            if (!$client->hasOrder( Order::TYPE_HW) && ($case['type'] == Order::TYPE_BOTH || $case['type']==Order::TYPE_HW)) {
                $manager->persist(new OrderHw($client, new \DateTime(rand(1, 10).' days ago')));
                echo "Added order HW to case {$case['number']}\n";
            }
            $manager->flush();
        }

    }
}