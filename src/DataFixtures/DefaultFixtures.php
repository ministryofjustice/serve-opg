<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Order;
use App\Entity\OrderHw;
use App\Entity\OrderPf;
use App\Entity\User;
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
     * @var string
     */
    private $fixturesEnabled;

    /**
     * DefaultFixtures constructor.
     * @param UserPasswordEncoderInterface $encoder
     * @param string $fixturesEnabled
     */
    public function __construct(UserPasswordEncoderInterface $encoder, string $fixturesEnabled)
    {
        $this->encoder = $encoder;
        $this->fixturesEnabled = $fixturesEnabled;
    }


    public function load(ObjectManager $manager)
    {
        if ($this->fixturesEnabled) {
//        @todo move into fixture file rather than hardcode here
            $users = <<<USERS
email=elvis.ciotti@digital.justice.gov.uk&password=Abcd1234
email=sean.privett@digital.justice.gov.uk&password=Abcd1234
email=shaun.lizzio@digital.justice.gov.uk&password=Abcd1234
email=robert.ford@digital.justice.gov.uk&password=Abcd1234
USERS;

//        @todo move into fixture file rather than hardcode here
            $cases = <<<CASES
number=4865226T&name=Peter Bloggs&type=both
number=88744573&name=Victoria Brady&type=PF
number=1267847T&name=Thomas Jefferson&type=HW
number=9258173T&name=Susan Grindle&type=both
number=03427488&name=Lia Shelton&type=PF
number=51934429&name=Abdullah Lang&type=PF
number=6554033T&name=Angela Walker&type=PF
number=98848454&name=Justine Henderson&type=PF
number=14564190&name=Marcus Cruz&type=PF
CASES;

            // users
            $repo = $manager->getRepository(User::class);
            $userLines = array_filter(explode("\n", $users));
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
            $caseLines = array_filter(explode("\n", $cases));
            foreach ($caseLines as $caseLine) {
                parse_str($caseLine, $case);
                if (!$client = $repo->findOneBy(['caseNumber' => $case['number']])) {
                    $client = new Client($case['number'], $case['name'], new \DateTime());
                    $manager->persist($client);
                    echo "Added case {$case['number']}\n";
                }

                if (!$client->hasOrder(Order::TYPE_PF) && ($case['type'] == Order::TYPE_BOTH || $case['type'] == Order::TYPE_PF)) {
                    $manager->persist(new OrderPf($client, new \DateTime(rand(11, 20).' days ago'), new \DateTime(rand(1, 10).' days ago')));
                    echo "Added order PF to case {$case['number']}\n";
                }
                if (!$client->hasOrder(Order::TYPE_HW) && ($case['type'] == Order::TYPE_BOTH || $case['type'] == Order::TYPE_HW)) {
                    $manager->persist(new OrderHw($client, new \DateTime(rand(11, 20).' days ago'), new \DateTime(rand(1, 10).' days ago')));
                    echo "Added order HW to case {$case['number']}\n";
                }
                $manager->flush();
            }
        }
    }
}
