<?php declare(strict_types=1);


namespace App\TestHelpers;


use App\Entity\Client;
use App\Entity\Order;
use App\Entity\OrderHw;
use App\Entity\OrderPf;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Yaml\Yaml;

class FixtureTestHelper
{
    private string $yamlFixtureLocation;

    private UserPasswordEncoderInterface $encoder;

    private EntityManagerInterface $em;

    private string $behatPassword;

    public function __construct(string $yamlFixtureLocation, UserPasswordEncoderInterface $encoder, EntityManagerInterface $em,  string $behatPassword)
    {
        $this->yamlFixtureLocation = $yamlFixtureLocation;
        $this->encoder = $encoder;
        $this->em = $em;
        $this->behatPassword = $behatPassword;
    }

    protected function parseYamlFixture(string $yamlFileName)
    {
        return Yaml::parse(file_get_contents($this->yamlFixtureLocation . $yamlFileName));
    }

    public function loadUserFixture(string $yamlFileName): void
    {
        $users = $this->parseYamlFixture($yamlFileName);

        foreach ($users as $key => $user) {
            $userModel = new User($user['email']);
            $password = $this->encoder->encodePassword($userModel, $this->behatPassword);
            $userModel->setPassword($password);
            $userModel->setRoles($user['roles']);
            $this->em->persist($userModel);
        }
        $this->em->flush();
    }

    public function loadCaseFixture(string $yamlFileName): void
    {
        $cases = $this->parseYamlFixture($yamlFileName);

        foreach ($cases as $index => $case) {
            $client = new Client($case['number'], $case['name'], new DateTime());
            $this->em->persist($client);
            $this->em->flush();

            if (!$client->hasOrder(Order::TYPE_PF) && ($case['type'] == Order::TYPE_BOTH || $case['type'] == Order::TYPE_PF)) {
                $this->em->persist(new OrderPf($client, new DateTime(rand(11, 20).' days ago'), new DateTime(rand(1, 10).' days ago'), strval(time() + mt_rand(1,1000000000))));
                $this->em->flush();
            }

            if (!$client->hasOrder(Order::TYPE_HW) && ($case['type'] == Order::TYPE_BOTH || $case['type'] == Order::TYPE_HW)) {
                $this->em->persist(new OrderHw($client, new DateTime(rand(11, 20).' days ago'), new DateTime(rand(1, 10).' days ago'), strval(time() + mt_rand(1,1000000000))));
                $this->em->flush();
            }
        }
    }
}
