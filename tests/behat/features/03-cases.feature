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


  Scenario: Add invalid deputy data
    Given I go to "/behat/case/empty/12345678/hw"
      And the response status code should be 200
    And I go to "/login"
    And I fill in the following:
      | login_username | behat@digital.justice.gov.uk |
      | login_password | Abcd1234                     |
    And I press "login_submit"
    # click on case
    When I follow "order-12345678-hw"
    When I fill in the following:
      | order_form_subType         | interim-order |
      | order_form_appointmentType | sole          |
    And I press "order_form_submit"
    Then the response status code should be 200
    And the url should match "order/\d+/summary"
    When I follow "add-deputy"
    And the url should match "case/order/\d+/deputy/add"
    # check form validation
    When I fill in the following:
      | deputy_form_deputyType       | lay |
    And I press "deputy_form_saveAndContinue"
    Then the following fields should have an error:
      | deputy_form_forename         |
      | deputy_form_surname          |
    When I fill in the following:
      | deputy_form_deputyType       | pa  |
    And I press "deputy_form_saveAndContinue"
    Then the response status code should be 200
    Then the following fields should have an error:
      | deputy_form_forename         |
      | deputy_form_surname          |
      | deputy_form_organisationName       |
    When I fill in the following:
      | deputy_form_deputyType       | prof  |
    And I press "deputy_form_saveAndContinue"
    Then the response status code should be 200
    Then the following fields should have an error:
      | deputy_form_forename          |
      | deputy_form_surname           |
      | deputy_form_organisationName |

  Scenario: Add valid deputy data
    Given I go to "/behat/case/empty/12345678/hw"
    And the response status code should be 200
    And I go to "/login"
    And I fill in the following:
      | login_username | behat@digital.justice.gov.uk |
      | login_password | Abcd1234                     |
    And I press "login_submit"
  # click on case
    When I follow "order-12345678-hw"
    When I fill in the following:
      | order_form_subType         | interim-order |
      | order_form_appointmentType | sole          |
    And I press "order_form_submit"
    Then the response status code should be 200
    And the url should match "order/\d+/summary"
    When I follow "add-deputy"
    And the url should match "case/order/\d+/deputy/add"
  # check form validation
    When I fill in the following:
      | deputy_form_deputyType       | lay |
      | deputy_form_forename         | Dep                                         |
      | deputy_form_surname          | Uty                                         |
      | deputy_form_emailAddress     | behat-12345678-depy1@digital.justice.gov.uk |
      | deputy_form_daytimeContactNumber    | 11111111111                          |
      | deputy_form_eveningContactNumber    | 22222222222                          |
      | deputy_form_mobileContactNumber    | +447933333333                         |
      | deputy_form_addressLine1     | Emb house                                   |
      | deputy_form_addressLine2     | victoria road                               |
      | deputy_form_addressLine3     | London                                      |
      | deputy_form_addressTown      | London                                      |
      | deputy_form_addressCounty    | London                                      |
      | deputy_form_addressPostcode  | SW1                                         |
    And I press "deputy_form_saveAndContinue"
    Then the response status code should be 200
    And the url should match "order/\d+/summary"
    And each text should be present in the corresponding region:
      | Dep Uty | deputy1-fullName     |
      | behat-12345678-depy1@digital.justice.gov.uk | deputy1-emailAddress |
      | lay                                                  | deputy1-deputyType   |
      | Emb house, victoria road, London, London, SW1        | deputy1-address      |
