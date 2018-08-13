<?php

namespace AppBundle\DataFixtures;

use AppBundle\Entity\Client;
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
        $users = array_filter(explode("\n", getenv('DC_FIXURES_USERS')));
        foreach ($users as $userStr) {
            parse_str($userStr, $user);
            if (!empty($user['email']) && !$repo->findOneBy(['email' => $user['email']])) {
                $u = new User($user['email']);
                $pass = $this->encoder->encodePassword($u, $user['password']);
                $u->setPassword($pass);
                $manager->persist($u);
                echo "Added {$user['email']}\n";
            }
        }

        // clients
        $repo = $manager->getRepository(Client::class);
        foreach ([
                     ['12345671', 'Peter Bloggs'],
                     ['22345672', 'Victoria Brady'],
                     ['32345673', 'Thomas Jefferson'],
                     ['32345673', 'Thomas Jefferson'],
                     ['42345674', 'Susan Grindle'],
                     ['62345676', 'Lia Shelton'],
                     ['72345677', 'Abdullah Lang'],
                     ['52345675', 'Angela Walker'],
                     ['92345679', 'Justine Henderson'],
                     ['82345678', 'Marcus Cruz'],
                 ] as $data) {
            if (!$repo->findOneBy(['caseNumber' => $data[0]])) {
                $client = new Client($data[0], $data[1], new \DateTime());
                $manager->persist($client);
            }
        }

        $manager->flush();
    }
}