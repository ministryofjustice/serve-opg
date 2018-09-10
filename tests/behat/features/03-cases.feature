Feature: cases

  Scenario: PA
    Given I go to "/behat/case/empty/12345678/pa"
    And the response status code should be 200
    And I go to "/login"
    And I fill in the following:
      | login_username | behat@digital.justice.gov.uk |
      | login_password | Abcd1234                     |
    And I press "login_submit"
    # click on case
    When I follow "order-12345678-pa"
    Then the response status code should be 200
    # check form validation
    When I fill in the following:
      | order_form_hasAssetsAboveThreshold |  |
      | order_form_subType                 |  |
      | order_form_appointmentType         |  |
    And I press "order_form_submit"
    Then the following fields should have an error:
      | order_form_hasAssetsAboveThreshold |
      | order_form_subType                 |
      | order_form_appointmentType         |
    # fill form in with valid data
    When I fill in the following:
      | order_form_hasAssetsAboveThreshold | no          |
      | order_form_subType                 | replacement |
      | order_form_appointmentType         | js          |
    And I press "order_form_submit"
    Then the response status code should be 200
    And each text should be present in the corresponding region:
      | No                               | has-assets    |
      | Replacement of discharged deputy | order-subtype |
      | Joint and several                | app-type      |

  Scenario: HW
    Given I go to "/behat/case/empty/12345678/hw"
    And the response status code should be 200
    And I go to "/login"
    And I fill in the following:
      | login_username | behat@digital.justice.gov.uk |
      | login_password | Abcd1234                     |
    And I press "login_submit"
    # click on case
    When I follow "order-12345678-hw"
    Then the response status code should be 200
    # check form validation
    When I fill in the following:
      | order_form_subType                 |  |
      | order_form_appointmentType         |  |
    And I press "order_form_submit"
    Then the following fields should have an error:
      | order_form_subType                 |
      | order_form_appointmentType         |
    # fill form in with valid data
    When I fill in the following:
      | order_form_subType         | interim-order |
      | order_form_appointmentType | sole          |
    And I press "order_form_submit"
    Then the response status code should be 200
    And I should not see the "has-assets" region
    And each text should be present in the corresponding region:
      | Interim order | order-subtype |
      | Sole          | app-type      |
