Feature: Served Order Report

  Scenario: Check I cannot access the report page
    Given I go to "/logout"
    When I go to "/report"
    Then I should be on "/login"
    When I go to "/report/cases"
    Then I should be on "/login"

  Scenario: Access report page as authenticated user
    Given I log in as "behat@digital.justice.gov.uk" with "correct password"
    When I go to "/report"
    Then the response status code should be 200

  Scenario: Access cases report page as authenticated user
    Given I log in as "behat@digital.justice.gov.uk" with "correct password"
    When I go to "/report/cases"
    Then the response status code should be 200
