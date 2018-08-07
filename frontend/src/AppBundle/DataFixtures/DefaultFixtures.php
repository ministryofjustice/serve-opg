<?php
namespace AppBundle\DataFixtures;

use Common\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class DefaultFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $users = array_filter(explode("\n", getenv('DC_FIXURES_USERS')));
        foreach($users as $userStr) {
            parse_str($userStr, $user);
            echo "Added {$user['email']}\n";
            if (!$manager->getRepository(User::class)->findOneBy(['email'=>$user['email']])) {
                $u = new User($user['email']);
                $u->setPassword($user['password']);
                $manager->persist($u);
            }
        }
        $manager->flush();
    }
}