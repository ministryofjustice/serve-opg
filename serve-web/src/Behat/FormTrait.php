<?php

namespace App\Behat;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;

trait FormTrait
{
    /**
     * @Then /^the form should be (?P<shouldBe>(valid|invalid))$/
     */
    public function theFormShouldBeOrNotBeValid($shouldBe): void
    {
        $this->assertResponseStatus(200);
        // added second css for new govuk error groups (see macro: errorSummary)
        $hasErrors = $this->getSession()->getPage()->has('css', '.form-group.form-group-error') ||
                     $this->getSession()->getPage()->has('css', '.govuk-error-summary');

        if ($shouldBe == 'valid' && $hasErrors) {
            throw new \RuntimeException('Errors found in the form. Zero expected');
        }

        if ($shouldBe == 'invalid' && !$hasErrors) {
            throw new \RuntimeException('No errors found in form. At least one expected');
        }
    }


    /**
     * @return array of IDs of input/select/textarea elements inside a  .form-group.form-group-error CSS class
     */
    private function getElementsIdsWithValidationErrors(): array
    {
        $ret = [];

        $errorRegions = $this->getSession()->getPage()->findAll('css', '.govuk-form-group--error');
        foreach ($errorRegions as $errorRegion) {
            $elementsWithErros = $errorRegion->findAll('xpath', "//*[name()='input' or name()='textarea' or name()='select']");
            foreach ($elementsWithErros as $elementWithError) {
                /* @var $found NodeElement */
                $ret[] = $elementWithError->getAttribute('id');
            }
        }

        return $ret;
    }

    /**
     * Check if the given elements (input/textarea inside each .behat-region-form-errors)
     *  are the only ones with errors.
     *
     * @Then the following fields should have an error:
     */
    public function theFollowingFieldsOnlyShouldHaveAnError(TableNode $table): void
    {
        $foundIdsWithErrors = $this->getElementsIdsWithValidationErrors();

        $fields = array_keys($table->getRowsHash());
        $untriggeredField = array_diff($fields, $foundIdsWithErrors);
        $unexpectedFields = array_diff($foundIdsWithErrors, $fields);

        if ($untriggeredField || $unexpectedFields) {
            $message = '';
            if ($untriggeredField) {
                $message .= " - Form fields not throwing error as expected: \n      " . implode(', ', $untriggeredField) . "\n";
            }
            if ($unexpectedFields) {
                $message .= " - Form fields unexpectedly throwing errors: \n      " . implode(', ', $unexpectedFields) . "\n";
            }

            throw new \RuntimeException($message);
        }
    }

    /**
     * @Then /^the following fields should have the corresponding values:$/
     */
    public function followingFieldsShouldHaveTheCorrespondingValues(TableNode $fields): void
    {
        foreach ($fields->getRowsHash() as $field => $value) {
            $this->assertFieldContains($field, $value);
        }
    }

    /**
     * @When I delete the user :name
     */
    public function deleteUser($name): void {
        $userLink = $this->getSession()->getPage()->findLink($name);
        $deleteLink = $userLink->find('xpath', '../ancestor::tr//a[normalize-space(text())="Delete"]');
        $deleteLink->click();
    }
}
