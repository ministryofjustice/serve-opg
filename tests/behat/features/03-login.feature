@deployment
Feature: login

  Scenario: login
    Given I go to "/login"
    Then the response status code should be 200
        # emtpy data
    When I fill in the following:
      | login_username |  |
      | login_password |  |
    And I press "login_submit"
    Then I should be on "/login"
        # wrong username
    When I fill in the following:
      | login_username | behatWRONG |
      | login_password | password |
    And I press "login_submit"
    Then I should be on "/login"
    Then I should see "Invalid credentials" in the "form-errors" region
        # wrong password
    When I fill in the following:
      | login_username | behat@digital.justice.gov.uk |
      | login_password | passwordWRONG |
    And I press "login_submit"
    Then I should be on "/login"
    # correct
    When I fill in the following:
      | login_username | behat@digital.justice.gov.uk |
      | login_password | Abcd1234 |
    And I press "login_submit"
    Then I should be on "/case"
    And the response status code should be 200
