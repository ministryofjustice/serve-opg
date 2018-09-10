Feature: cases

  Scenario: PA
    Given I go to "/behat/fixture-reset"
    And the response status code should be 200
    And I am logged in as behat user
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
    Given I go to "/behat/fixture-reset"
    And print last response
    And the response status code should be 200
    And I am logged in as behat user
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

  Scenario: search
    Given I go to "/behat/fixture-reset"
    And print last response
    And I am logged in as behat user
    # fake q
    When I fill in "search" with "NOT EXISTING"
    And I press "search_submit"
    Then I should not see the "order-12345678-pa" region
    And I should not see the "order-12345678-hw" region
    # real search
    When I fill in "q" with "12345678"
    And I press "search_submit"
    # served tab has no results
    And I click on "served-tab"
    Then I should not see the "order-12345678-pa" region
    And I should not see the "order-12345678-hw" region
    # pending tab has results
    When I click on "pending-tab"
    Then I should see the "order-12345678-pa" region
    And I should see the "order-12345678-hw" region




    # create deputy
#    When I fill in the following:
#      | deputy_appointmentType  | sole                                        |
#      | deputy_deputyType       | lay                                         |
#      | deputy_forename         | Dep                                         |
#      | deputy_surname          | Uty                                         |
#      | deputy_emailAddress     | behat-12345678-depy1@digital.justice.gov.uk |
#      | deputy_contactNumber    | 38745837468347                              |
#      | deputy_organisationName | org1                                        |
#      | deputy_addressLine1     | Emb house                                   |
#      | deputy_addressLine2     | victoria road                               |
#      | deputy_addressLine3     | London                                      |
#      | deputy_addressTown      | London                                      |
#      | deputy_addressCounty    | London                                      |
#      | deputy_addressCountry   | UK                                          |
#      | deputy_addressPostcode  | SW1                                         |
#      | deputy_deputyAnswerQ2_6 | answered-yes                                |
#      | deputy_deputyS4Response | no                                          |
#    And I press "deputy_saveAndContinue"
#    Then the response status code should be 200



