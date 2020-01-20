Feature: User management

  Scenario: Add new user
    Given I log in as "behat+admin@digital.justice.gov.uk" with password "Abcd1234"
    When I go to "/users"
    And I follow "Add new user"
    And I fill in the following:
      | user_form_email       | behat+newuser@digital.justice.gov.uk |
      | user_form_firstName   | Andreas                              |
      | user_form_lastName    | Standaert                            |
      | user_form_phoneNumber | 07123456789                          |
    And I press "Add user"
    Then the response status code should be 200
    And I should see "We've emailed a link to behat+newuser@digital.justice.gov.uk"
    When I go to "/users"
    Then I should see "Andreas Standaert"
    When I follow "Andreas Standaert"
    Then I should see "behat+newuser@digital.justice.gov.uk"
    And I should see "07123456789"

  Scenario: Edit user
    Given I log in as "behat+admin@digital.justice.gov.uk" with password "Abcd1234"
    When I go to "/users"
    And I follow "Andreas Standaert"
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
    Given I log in as "behat+admin@digital.justice.gov.uk" with password "Abcd1234"
    When I go to "/users"
    And I delete the user "Joaquin Lubic"
    Then the response status code should be 200
    And I should be on "/users"
    And I should not see "Joaquin Lubic"
