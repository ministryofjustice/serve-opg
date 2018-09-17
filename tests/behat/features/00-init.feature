Feature: prechecks

    Scenario: add behat (see details in BehatController constants)
        Given I go to "/behat/behat-user-upsert"
        And print last response
        And the response status code should be 200
