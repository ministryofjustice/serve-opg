<?php

namespace AppBundle\Behat;

use AppBundle\Controller\BehatController;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Behat context class.
 */
class FeatureContext extends MinkContext implements KernelAwareContext
{
    use RegionLinksTrait;
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
        $this->fillField('login_username', BehatController::BEHAT_EMAIL);
        $this->fillField('login_password', BehatController::BEHAT_PASSWORD);
        $this->pressButton('login_submit');
    }

}
