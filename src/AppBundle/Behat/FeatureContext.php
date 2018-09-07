<?php

namespace AppBundle\Behat;

use AppBundle\Entity\Client;
use AppBundle\Entity\Order;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Behat context class.
 */
class FeatureContext extends MinkContext implements KernelAwareContext
{
    use RegionTrait;
    use FormTrait;

    /**
     * @var EntityManager
     */
    private $em;

    public function setKernel(KernelInterface $kernel)
    {
        $this->em = $kernel->getContainer()->get(EntityManager::class);
    }

    /**
     * @Given I am logged in as behat user
     */
    public function iAmLoggedInAsBehatUser()
    {
        $this->visit("/login");
        $this->fillField('login_username', 'behat@digital.justice.gov.uk');
        $this->fillField('login_password', 'Abcd1234');
        $this->pressButton('login_submit');
    }

}
