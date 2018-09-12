Feature: prechecks

    Scenario: check app status and init db
        Given I go to "/behat/fixture-reset"
        And print last response
        And the response status code should be 200
