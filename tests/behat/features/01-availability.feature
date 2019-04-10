@smoke
Feature: prechecks

    Scenario: check app status
        Given I go to "/manage/availability"
        Then the response status code should be 200
        And sirius status should not be unavailable
        And sm status should be 200


    Scenario: check deployed versions
        When I go to "/manage/version"
        Then the current versions should be shown
        And the response status code should be 200
        And the Content-Type response header should be application/json
