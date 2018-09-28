Feature: prechecks

    Scenario: add behat (see details in BehatController constants)
        Given I go to "/behat/behat-user-upsert"
        And print last response
        And the response status code should be 200

    Scenario: Reset behat cases
        When I go to "/behat/reset-behat-orders"
        Then the response status code should be 200

