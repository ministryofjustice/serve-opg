Feature: cases

  Scenario: cases
    Given I go to "/login"
    And I fill in the following:
      | login_username | behat@digital.justice.gov.uk |
      | login_password | Abcd1234 |
    And I press "login_submit"
    # click on case
    And I follow "add-order-12345678"
    # create order
    And I fill in the following:
      | order_type | both |
      | order_subType |replacement |
      | order_hasAssetsAboveThreshold |no |
    And I press "order_submit"
    Then the response status code should be 200
    # create deputy
    # ...

