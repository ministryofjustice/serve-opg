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
class FeatureContext extends MinkContext
{
    use RegionLinksTrait;
    use FormTrait;
    use DebugTrait;

    /**
     * @Then /^the (?P<name>(.*)) response header should be (?P<value>(.*))$/
     */
    public function theHeaderContains($name, $value)
    {
        $this->assertSession()->responseHeaderContains($name, $value);
    }

    /**
     * @Then the current version should be shown
     */
    public function theCurrentVersionIsShown()
    {
        $this->assertResponseContains(getenv("APP_VERSION"));
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

    /**
     * @Then /^the order should be (?P<shouldBe>(servable|unservable))$/
     */
    public function theOrderIsOrNotServable($shouldBe)
    {
        $this->assertResponseStatus(200);

        if ($shouldBe == 'servable') {
            $this->assertSession()->elementExists('css', '#serve_order_button');
        }

        if ($shouldBe == 'unservable') {
            $this->assertSession()->elementNotExists('css', '#serve_order_button');
        }
    }


}
