@smoke
Feature: prechecks

    Scenario: check app status
        Given I go to "/manage/availability"
        And the response status code should be 200

    Scenario: check app version
        When I go to "/manage/version"
        Then the current version should be shown
        And the response status code should be 200
        And the Content-Type response header should be application/json
