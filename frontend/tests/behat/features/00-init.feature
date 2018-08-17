# @deployment
Feature: prechecks

    Scenario: check app status
        Given I go to "/manage/availability"
        And the response status code should be 200
