<?php declare(strict_types=1);

namespace App\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Behat\MinkExtension\Context\MinkContext;
use Exception;

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
        $this->behatPasswordNew = $this->behatPassword . "9";
    }

    /**
     * @Then /^the (?P<name>(.*)) response header should be (?P<value>(.*))$/
     */
    public function theHeaderContains($name, $value): void
    {
        $this->assertSession()->responseHeaderContains($name, $value);
    }

    /**
     * @Then the current versions should be shown
     */
    public function theCurrentVersionsAreShown(): void
    {
        $this->assertResponseContains(json_encode([
            'application' => getenv("APP_VERSION"),
            'web' => getenv("WEB_VERSION"),
            'infrastructure' => getenv("INFRA_VERSION")
        ]));
    }

    /**
     * @Given I log in as :user with correct password
     * @param $user
     */
    public function iLogInAsCorrect($user): void
    {
        $this->visit("/login");
        $this->fillField('email', $user);
        $this->fillField('password', $this->behatPassword);
        $this->pressButton('login_submit');
    }

    /**
     * @Given I log in as :user with wrong password
     * @param $user
     */
    public function iLogInAsWrong($user): void
    {
        $this->visit("/login");
        $this->fillField('inputEmail', $user);
        $this->fillField('password', 'wrong password');
        $this->pressButton('login_submit');
    }

    /**
     * @Given I log in as :user with new password
     * @param $user
     */
    public function iLogInAsNew($user): void
    {
        $this->visit("/login");
        $this->fillField('email', $user);
        $this->fillField('password', $this->behatPasswordNew);
        $this->pressButton('login_submit');
    }

    /**
     * @Given I log in as :user with no password
     * @param $user
     */
    public function iLogInAsNone($user): void
    {
        $this->visit("/login");
        $this->fillField('email', $user);
        $this->fillField('password', '');
        $this->pressButton('login_submit');
    }
    
    /**
     * @Then /^the order should be (?P<shouldBe>(servable|unservable))$/
     */
    public function theOrderIsOrNotServable($shouldBe): void
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
    public function statusShouldBe($service, $status): void
    {
        $this->assertResponseContains("\"$service\":$status");
    }

    /**
     * @Then :service status should not be :status
     */
    public function statusShouldNotBe($service, $status): void
    {
        $this->assertResponseNotContains("\"$service\":$status");
        $this->assertResponseNotContains("\"$service\":\"$status\"");
    }

    /**
     * @Then auto complete should be disabled
     */
    public function autoCompleteDisabled(): void
    {
        $page = $this->getSession()->getPage()->find('css', '#login')->getAttribute('autocomplete');
        $this->assertResponseContains('off');
    }

    /**
     * @When /^I fill in new password details$/
     */
    public function iFillInNewPasswordDetails(): void
    {
        $this->fillField('password_change_form_password_first', $this->behatPasswordNew);
        $this->fillField('password_change_form_password_second', $this->behatPasswordNew);
        $this->pressButton('password_change_form_submit');
    }

    /**
     * @Then :orderType order :caseNumber should have the following values under column headers:
     */
    public function orderShouldHaveValuesUnderHeaders(TableNode $table, string $orderType, string $caseNumber): void
    {
        $this->assertValuesAreInCorrectColumns($table);
        $this->assertOrderDetailsDisplayed($table, $orderType, $caseNumber);
    }

    private function assertValuesAreInCorrectColumns(TableNode $table): void
    {
        foreach ($table->getRowsHash() as $expectedTableHeader => $expectedTableValue) {
            $foundColumnValues = $this->getSession()->getPage()->findAll(
                'xpath',
                "//table/tbody/tr/td[count(//table/thead/tr/th[.='$expectedTableHeader']/preceding-sibling::th)+1]"
            );

            $columnValues = [];

            if (empty($foundColumnValues)) {
                throw new Exception("Could not find a column with header '$expectedTableHeader'");
            }

            foreach($foundColumnValues as $foundColumnValue) {
                $columnValues[] = trim($foundColumnValue->getText());
            }

            $valueUnderExpectedHeader = in_array($expectedTableValue, $columnValues);

            if (!$valueUnderExpectedHeader) {
                throw new Exception(
                    sprintf(
                        '"%s" was not found under the column "%s". Found values %s',
                        $expectedTableValue,
                        $expectedTableHeader,
                        implode(', ', $columnValues)
                    )
                );
            }
        }
    }

    private function assertOrderDetailsDisplayed(TableNode $table, string $orderType, string $caseNumber): void
    {
        $foundRows = $this->getSession()->getPage()->findAll('xpath', "//a[contains(.,'$caseNumber')]/../..");

        $orderRow = null;

        foreach($foundRows as $foundRow) {
            if (str_contains(trim($foundRow->getHtml()), $orderType)) {
                $orderRow = $foundRow;
                break;
            }
        }

        if (is_null($orderRow)) {
            throw new Exception("Could not find an order with case number '$caseNumber' and type '$orderType'");
        }

        foreach ($table->getRowsHash() as $expectedTableHeader => $expectedTableValue) {
            assert(str_contains($orderRow->getHtml(), $expectedTableValue), "Could not find expected order detail '$expectedTableHeader' with value '$expectedTableValue' in the table row associated with order $caseNumber");
        }
    }
}
