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
    /**
     * @var string
     */
    private $yamlFixtureLocation;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(string $yamlFixtureLocation, UserPasswordEncoderInterface $encoder, EntityManagerInterface $em)
    {
        $this->yamlFixtureLocation = $yamlFixtureLocation;
        $this->encoder = $encoder;
        $this->em = $em;
    }

    protected function parseYamlFixture(string $yamlFileName)
    {
        return Yaml::parse(file_get_contents($this->yamlFixtureLocation . $yamlFileName));
    }

    public function loadUserFixture(string $yamlFileName)
    {
        $users = $this->parseYamlFixture($yamlFileName);

        foreach ($users as $key => $user) {
            $userModel = new User($user['email']);
            $password = $this->encoder->encodePassword($userModel, $user['password']);
            $userModel->setPassword($password);
            $userModel->setRoles($user['roles']);
            $this->em->persist($userModel);
        }
        $this->em->flush();
    }

    public function loadCaseFixture(string $yamlFileName)
    {
        $cases = $this->parseYamlFixture($yamlFileName);

        foreach ($cases as $case) {
            $client = new Client($case['number'], $case['name'], new DateTime());
            $this->em->persist($client);
            $this->em->flush();

            if (!$client->hasOrder(Order::TYPE_PF) && ($case['type'] == Order::TYPE_BOTH || $case['type'] == Order::TYPE_PF)) {
                $this->em->persist(new OrderPf($client, new DateTime(rand(11, 20).' days ago'), new DateTime(rand(1, 10).' days ago')));
                $this->em->flush();
            }

            if (!$client->hasOrder(Order::TYPE_HW) && ($case['type'] == Order::TYPE_BOTH || $case['type'] == Order::TYPE_HW)) {
                $this->em->persist(new OrderHw($client, new DateTime(rand(11, 20).' days ago'), new DateTime(rand(1, 10).' days ago')));
                $this->em->flush();
            }
        }
    }
}
