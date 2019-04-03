Feature: Served Order Report

  Scenario: unauthenticated user can't access the report page
    Given I go to "/logout"
    When I go to "/report"
    Then the response status code should be 302

  Scenario: Access report page as authenticated user
    Given I log in as "behat@digital.justice.gov.uk" with password "Abcd1234"
    When I go to "/report"
    Then the response status code should be 200
