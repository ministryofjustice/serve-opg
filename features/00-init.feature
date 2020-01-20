Feature: prechecks

    Scenario: add behat (see details in BehatController constants)
        Given I go to "/behat/behat-user-upsert"
        And the response status code should be 200

    Scenario: Reset behat users
        When I go to "/behat/reset-behat-test-users"
        Then the response status code should be 200

    Scenario: Reset behat cases
        When I go to "/behat/reset-behat-orders"
        Then the response status code should be 200

    Scenario: Reset brute force attempts
        When I go to "/behat/reset-brute-force-attempts-logger"
        Then the response status code should be 200

    Scenario: Login page works OK
        When I go to "/login"
        Then the response status code should be 200
