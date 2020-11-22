Feature: User management

  Scenario: Add new user
    Given I log in as "behat+admin@digital.justice.gov.uk" with "correct password"
    When I go to "/users"
    And I follow "Add new user"
    Then I should be on "/users/add"
    # Cannot actually add a user as it relies on sending an email

  Scenario: Edit user
    Given I log in as "behat+admin@digital.justice.gov.uk" with "correct password"
    When I go to "/users"
    And I follow "behat+user-management@digital.justice.gov.uk"
    And I follow "Edit details"
    And I fill in the following:
      | user_form_firstName   | Joaquin    |
      | user_form_lastName    | Lubic      |
      | user_form_roleName    | ROLE_ADMIN |
    And I press "Update user"
    Then the response status code should be 200
    And I should be on "/users"
    And I should see "Joaquin Lubic"
    And I should not see "Andreas Standaert"
    When I follow "Joaquin Lubic"
    Then I should see "Admin"

  Scenario: Delete user
    Given I log in as "behat+admin@digital.justice.gov.uk" with "correct password"
    When I go to "/users"
    And I delete the user "Joaquin Lubic"
    Then the response status code should be 200
    And I should be on "/users"
    And I should not see "Joaquin Lubic"
