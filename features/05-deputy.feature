Feature: deputy

  Scenario: HW: add invalid deputy data
    Given I log in as "behat@digital.justice.gov.uk" with password "Abcd1234"
    When I follow "order-93559316-HW"
    Then the url should match "order/\d+/summary"
    When I follow "add-deputy"
    And the url should match "order/\d+/deputy/add"
        # check form validation
    And I fill in the following:
      | deputy_form_deputyType | LAY |
    And I press "deputy_form_saveAndContinue"
    Then the following fields should have an error:
      | deputy_form_forename |
      | deputy_form_surname  |
    When I fill in the following:
      | deputy_form_deputyType | PUBLIC_AUTHORITY |
    And I press "deputy_form_saveAndContinue"
    Then the response status code should be 200
    And the following fields should have an error:
      | deputy_form_forename         |
      | deputy_form_surname          |
    When I fill in the following:
      | deputy_form_deputyType | PROFESSIONAL |
    And I press "deputy_form_saveAndContinue"
    Then the response status code should be 200
    Then the following fields should have an error:
      | deputy_form_forename         |
      | deputy_form_surname          |

  Scenario: HW order: add valid deputy data
    Given I log in as "behat@digital.justice.gov.uk" with password "Abcd1234"
    When I follow "order-93559316-HW"
    When I follow "add-deputy"
      # check form validation
    When I fill in the following:
      | deputy_form_deputyType           | LAY                                         |
      | deputy_form_forename             | Dep                                         |
      | deputy_form_surname              | Uty                                         |
      | deputy_form_emailAddress         | behat-12345678-depy1@digital.justice.gov.uk |
      | deputy_form_dateOfBirth_day      | 01                                          |
      | deputy_form_dateOfBirth_month    | 11                                          |
      | deputy_form_dateOfBirth_year     | 1999                                        |
      | deputy_form_daytimeContactNumber | 11111111111                                 |
      | deputy_form_eveningContactNumber | 22222222222                                 |
      | deputy_form_mobileContactNumber  | +447933333333                               |
      | deputy_form_addressLine1         | Emb house                                   |
      | deputy_form_addressLine2         | victoria road                               |
      | deputy_form_addressTown          | London                                      |
      | deputy_form_addressCounty        | London                                      |
      | deputy_form_addressPostcode      | SW1                                         |
    And I press "deputy_form_saveAndContinue"
    Then the response status code should be 200
    And the url should match "order/\d+/summary"
    And I should see "Dep Uty"
    And I should see "behat-12345678-depy1@digital.justice.gov.uk"
    And I should see "1 November 1999"
    And I should see "lay"
    And I should see "Emb house, victoria road, London, London, SW1"
    And the order should be unservable

  Scenario: HW order: edit deputy data
    Given I log in as "behat@digital.justice.gov.uk" with password "Abcd1234"
    When I follow "order-93559316-HW"
    Then I follow "edit-deputy-1"
  # check form validation
    When I fill in the following:
      | deputy_form_deputyType           | LAY                                         |
      | deputy_form_forename             | DepE                                         |
      | deputy_form_surname              | UtyE                                          |
      | deputy_form_emailAddress         | behat-12345678-depy1E@digital.justice.gov.uk |
      | deputy_form_dateOfBirth_day      | 01                                          |
      | deputy_form_dateOfBirth_month    | 11                                          |
      | deputy_form_dateOfBirth_year     | 1999                                        |
      | deputy_form_daytimeContactNumber | 11111111119                                 |
      | deputy_form_eveningContactNumber | 22222222229                                 |
      | deputy_form_mobileContactNumber  | +447933333339                               |
      | deputy_form_addressLine1         | Emb houseE                                  |
      | deputy_form_addressLine2         | victoria roadE                               |
      | deputy_form_addressTown          | LondonE2                                      |
      | deputy_form_addressCounty        | LondonE3                                      |
      | deputy_form_addressPostcode      | SW1 E                                         |
    And I press "deputy_form_saveAndContinue"
    Then the response status code should be 200
    And the url should match "order/\d+/summary"
    And I should see "DepE UtyE"
    And I should see "behat-12345678-depy1E@digital.justice.gov.uk"
    And I should see "1 November 1999"
    And I should see "lay"
    And I should see "Emb houseE, victoria roadE, LondonE2, LondonE3, SW1 E"
    And the order should be unservable


  Scenario: HW order: remove deputy
    Given I log in as "behat@digital.justice.gov.uk" with password "Abcd1234"
    When I follow "order-93559316-HW"
    Then I follow "add-deputy"
    When I fill in the following:
      | deputy_form_deputyType           | LAY                                         |
      | deputy_form_forename             | Dep2                                         |
      | deputy_form_surname              | Uty2                                         |
      | deputy_form_emailAddress         | behat-12345678-depy2@digital.justice.gov.uk |
      | deputy_form_dateOfBirth_day      | 01                                          |
      | deputy_form_dateOfBirth_month    | 11                                          |
      | deputy_form_dateOfBirth_year     | 1999                                        |
      | deputy_form_daytimeContactNumber | 11111111111                                 |
      | deputy_form_eveningContactNumber | 22222222222                                 |
      | deputy_form_mobileContactNumber  | +447933333333                               |
      | deputy_form_addressLine1         | Emb house                                   |
      | deputy_form_addressLine2         | victoria road                               |
      | deputy_form_addressTown          | London                                      |
      | deputy_form_addressCounty        | Surrey                                      |
      | deputy_form_addressPostcode      | SW1                                         |
    And I press "deputy_form_saveAndContinue"
    Then the response status code should be 200
    And the url should match "order/\d+/summary"
    And I should see "Dep2 Uty2"
    And I should see "behat-12345678-depy2@digital.justice.gov.uk"
    And I should see "1 November 1999"
    And I should see "lay"
    And I should see "Emb house, victoria road, London, Surrey, SW1"
    Then I follow "delete-deputy-2"
    And I press "confirmation_form_submit"
    Then the response status code should be 200
    And the url should match "order/\d+/summary"
    And I should not see "deputy2-fullName"


  Scenario: PF order: add one deputy (just type, first and lastname)
    Given I log in as "behat@digital.justice.gov.uk" with password "Abcd1234"
    When I follow "order-93559316-PF"
    When I follow "add-deputy"
      # check form validation
    When I fill in the following:
      | deputy_form_deputyType | PUBLIC_AUTHORITY |
      | deputy_form_forename   | PfPADep |
      | deputy_form_surname   | Uty |
    And I press "deputy_form_saveAndContinue"
    Then the form should be valid
    And I should see "PfPADep Uty"
    And the order should be unservable

  Scenario: HW order: add one deputy (just type, first and lastname)
    Given I log in as "behat@digital.justice.gov.uk" with password "Abcd1234"
    When I follow "order-93559316-HW"
    When I follow "add-deputy"
  # check form validation
    When I fill in the following:
      | deputy_form_deputyType | PUBLIC_AUTHORITY |
      | deputy_form_forename   | HwPADep |
      | deputy_form_surname   | Uty |
    And I press "deputy_form_saveAndContinue"
    Then the form should be valid
    And I should see "HwPADep Uty"
    And the order should be unservable
