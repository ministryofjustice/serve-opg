<?php

namespace AppBundle\Behat;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;

trait FormTrait
{
    /**
     * @Then /^the form should be (?P<shouldBe>(valid|invalid))$/
     */
    public function theFormShouldBeOrNotBeValid($shouldBe)
    {
        $this->assertResponseStatus(200);
        $hasErrors = $this->getSession()->getPage()->has('css', '.form-group.form-group-error');

        if ($shouldBe == 'valid' && $hasErrors) {
            throw new \RuntimeException('Errors found in the form. Zero expected');
        }

        if ($shouldBe == 'invalid' && !$hasErrors) {
            throw new \RuntimeException('No errors found in form. At elast one expected');
        }
    }


    /**
     * @return array of IDs of input/select/textarea elements inside a  .form-group.form-group-error CSS class
     */
    private function getElementsIdsWithValidationErrors()
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
    public function theFollowingFieldsOnlyShouldHaveAnError(TableNode $table)
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
    public function followingFieldsShouldHaveTheCorrespondingValues(TableNode $fields)
    {
        foreach ($fields->getRowsHash() as $field => $value) {
            $this->assertFieldContains($field, $value);
        }
    }

}
