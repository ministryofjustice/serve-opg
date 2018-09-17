Feature: deputy

  Scenario: HW: add invalid deputy data
    Given I am logged in as behat user
    When I follow "order-12345678-hw"
    Then the url should match "order/\d+/summary"
    When I follow "add-deputy"
    And the url should match "order/\d+/deputy/add"
        # check form validation
    When I fill in the following:
      | deputy_form_deputyType | lay |
    And I press "deputy_form_saveAndContinue"
    Then the following fields should have an error:
      | deputy_form_forename |
      | deputy_form_surname  |
    When I fill in the following:
      | deputy_form_deputyType | pa |
    And I press "deputy_form_saveAndContinue"
    Then the response status code should be 200
    Then the following fields should have an error:
      | deputy_form_forename         |
      | deputy_form_surname          |
      | deputy_form_organisationName |
    When I fill in the following:
      | deputy_form_deputyType | prof |
    And I press "deputy_form_saveAndContinue"
    Then the response status code should be 200
    Then the following fields should have an error:
      | deputy_form_forename         |
      | deputy_form_surname          |
      | deputy_form_organisationName |

  Scenario: HW order: add valid deputy data
    Given I am logged in as behat user
    When I follow "order-12345678-hw"
    When I follow "add-deputy"
      # check form validation
    When I fill in the following:
      | deputy_form_deputyType           | lay                                         |
      | deputy_form_forename             | Dep                                         |
      | deputy_form_surname              | Uty                                         |
      | deputy_form_emailAddress         | behat-12345678-depy1@digital.justice.gov.uk |
      | deputy_form_daytimeContactNumber | 11111111111                                 |
      | deputy_form_eveningContactNumber | 22222222222                                 |
      | deputy_form_mobileContactNumber  | +447933333333                               |
      | deputy_form_addressLine1         | Emb house                                   |
      | deputy_form_addressLine2         | victoria road                               |
      | deputy_form_addressLine3         | London                                      |
      | deputy_form_addressTown          | London                                      |
      | deputy_form_addressCounty        | London                                      |
      | deputy_form_addressPostcode      | SW1                                         |
    And I press "deputy_form_saveAndContinue"
    Then the response status code should be 200
    And the url should match "order/\d+/summary"
    And each text should be present in the corresponding region:
      | Dep Uty                                       | deputy1-fullName     |
      | behat-12345678-depy1@digital.justice.gov.uk   | deputy1-emailAddress |
      | lay                                           | deputy1-deputyType   |
      | Emb house, victoria road, London, London, SW1 | deputy1-address      |
    And the order should be unservable


  Scenario: PA order: add one deputy (just type, first and lastname)
    Given I am logged in as behat user
    When I follow "order-12345678-pa"
    When I follow "add-deputy"
      # check form validation
    When I fill in the following:
      | deputy_form_deputyType | lay |
      | deputy_form_forename   | PaDep |
      | deputy_form_surname   | Uty |
    And I press "deputy_form_saveAndContinue"
    Then the form should be valid
    And each text should be present in the corresponding region:
      | PaDep Uty | deputy1-fullName |
    And the order should be unservable