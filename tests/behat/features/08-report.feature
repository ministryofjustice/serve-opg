Feature: Served Order Report

  Scenario: Report page
    Given I go to "/report"
    Then the response status code should be 200
