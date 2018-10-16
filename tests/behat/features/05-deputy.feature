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
    And each text should be present in the corresponding region:
      | Dep Uty                                       | deputy1-fullName     |
      | behat-12345678-depy1@digital.justice.gov.uk   | deputy1-emailAddress |
      | 1 November 1999                               | deputy1-dateOfBirth  |
      | lay                                           | deputy1-deputyType   |
      | Emb house, victoria road, London, London, SW1 | deputy1-address      |
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
    And each text should be present in the corresponding region:
      | DepE UtyE                                             | deputy1-fullName     |
      | behat-12345678-depy1E@digital.justice.gov.uk          | deputy1-emailAddress |
      | 1 November 1999                                       | deputy1-dateOfBirth  |
      | lay                                                   | deputy1-deputyType   |
      | Emb houseE, victoria roadE, LondonE2, LondonE3, SW1 E | deputy1-address      |
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
    And each text should be present in the corresponding region:
      | Dep2 Uty2                                             | deputy2-fullName     |
      | behat-12345678-depy2@digital.justice.gov.uk           | deputy2-emailAddress |
      | 1 November 1999                                       | deputy2-dateOfBirth  |
      | lay                                                   | deputy2-deputyType   |
      | Emb house, victoria road, London, Surrey, SW1           | deputy2-address      |
    Then I follow "delete-deputy-2"
    And I press "confirmation_form_submit"
    Then the response status code should be 200
    And the url should match "order/\d+/summary"
    And I should not see "deputy2-fullName"


  Scenario: PA order: add one deputy (just type, first and lastname)
    Given I log in as "behat@digital.justice.gov.uk" with password "Abcd1234"
    When I follow "order-93559316-PF"
    When I follow "add-deputy"
      # check form validation
    When I fill in the following:
      | deputy_form_deputyType | LAY |
      | deputy_form_forename   | PaDep |
      | deputy_form_surname   | Uty |
    And I press "deputy_form_saveAndContinue"
    Then the form should be valid
    And each text should be present in the corresponding region:
      | PaDep Uty | deputy1-fullName |
    And the order should be unservable
