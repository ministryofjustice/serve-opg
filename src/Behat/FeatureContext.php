<?php declare(strict_types=1);

namespace App\Behat;

use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\MinkContext;

/**
 * Behat context class.
 */
class FeatureContext extends MinkContext implements Context
{
    use RegionLinksTrait;
    use FormTrait;
    use DebugTrait;
    use SiriusTrait;
    use NotifyTrait;

    /**
     * @var string
     */
    private string $behatPasswordNew;
    /**
     * @var string
     */
    private string $behatPassword;

    public function __construct()
    {
        $this->behatPassword = getenv("BEHAT_PASSWORD");
        $this->behatPasswordNew = getenv("BEHAT_PASSWORD_NEW");
    }

    /**
     * @Then /^the (?P<name>(.*)) response header should be (?P<value>(.*))$/
     */
    public function theHeaderContains($name, $value)
    {
        $this->assertSession()->responseHeaderContains($name, $value);
    }

    /**
     * @Then the current versions should be shown
     */
    public function theCurrentVersionsAreShown()
    {
        $this->assertResponseContains(json_encode([
            'application' => getenv("APP_VERSION"),
            'web' => getenv("WEB_VERSION"),
            'infrastructure' => getenv("INFRA_VERSION")
        ]));
    }

    /**
     * @Given I log in as :user with :password
     */
    public function iLogInAs($user, $password)
    {
        if ($password == "correct password") {
            $password_to_use = $this->behatPassword;
        } elseif ($password == "new password") {
            $password_to_use = $this->behatPasswordNew;
        }
        else {
            $password_to_use = 'wrong password';
        }
        $this->visit("/login");
        $this->fillField('login_username', $user);
        $this->fillField('login_password', $password_to_use);
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

    /**
     * @Then :service status should be :status
     */
    public function statusShouldBe($service, $status)
    {
        $this->assertResponseContains("\"$service\":$status");
    }

    /**
     * @Then :service status should not be :status
     */
    public function statusShouldNotBe($service, $status)
    {
        $this->assertResponseNotContains("\"$service\":$status");
        $this->assertResponseNotContains("\"$service\":\"$status\"");
    }

    /**
     * @Then auto complete should be disabled
     */
    public function autoCompleteDisabled()
    {
        $page = $this->getSession()->getPage()->find('css', '#login')->getAttribute('autocomplete');
        $this->assertResponseContains('off');
    }

    /**
     * @When /^I fill in new password details$/
     */
    public function iFillInNewPasswordDetails()
    {
        $this->fillField('password_change_form_password_first', $this->behatPasswordNew);
        $this->fillField('password_change_form_password_second', $this->behatPasswordNew);
        $this->pressButton('password_change_form_submit');
    }
}
