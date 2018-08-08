<?php
namespace AppBundle\DataFixtures;

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
        $repo = $manager->getRepository(User::class);
        $users = array_filter(explode("\n", getenv('DC_FIXURES_USERS')));
        foreach($users as $userStr) {
            parse_str($userStr, $user);
            if (!empty($user['email']) && !$repo->findOneBy(['email'=>$user['email']])) {
                $u = new User($user['email']);
                $pass = $this->encoder->encodePassword($u, $user['password']);
                $u->setPassword($pass);
                $manager->persist($u);
                echo "Added {$user['email']}\n";
            }
        }
        $manager->flush();
    }
}