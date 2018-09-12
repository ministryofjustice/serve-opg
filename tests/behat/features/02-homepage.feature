@smoke
Feature: homepage

    Scenario: homepage
        Given I go to "/"
        And the response status code should be 200
        And I should see "Digicop"
