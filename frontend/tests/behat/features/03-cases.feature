Feature: cases

  Scenario: cases
    Given The case "12345678" has no orders
    And I go to "/login"
    And I fill in the following:
      | login_username | behat@digital.justice.gov.uk |
      | login_password | Abcd1234                     |
    And I press "login_submit"
    # click on case
    When I follow "add-order-12345678"
    Then the response status code should be 200
    # create order
    When I fill in the following:
      | order_type                    | both        |
      | order_subType                 | replacement |
      | order_hasAssetsAboveThreshold | no          |
    And I press "order_submit"
    Then the response status code should be 200
    # create deputy
    # ...

