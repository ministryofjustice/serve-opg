# @deployment
Feature: prechecks

    Scenario: check app status and init db
        Given I go to "/behat/fixture-reset"
        And print last response
        And I go to "/manage/availability"
        And the response status code should be 200
