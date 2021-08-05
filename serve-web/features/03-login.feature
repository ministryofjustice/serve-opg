@deployment
Feature: login

  Scenario: login
    Given I go to "/login"
    Then the response status code should be 200
        # emtpy data
    When I log in as "" with no password
    Then I should be on "/login"
        # wrong username
    When I log in as "wrongUser" with wrong password
    Then I should be on "/login"
    And I should see "Username could not be found." in the "form-errors" region
      # wrong password
    When I log in as "behat@digital.justice.gov.uk" with wrong password
    And I press "login_submit"
    Then I should be on "/login"
    And I should see "Invalid credentials." in the "form-errors" region
    # correct
    When I log in as "behat@digital.justice.gov.uk" with correct password
    Then I should be on "/case"
    And the response status code should be 200
