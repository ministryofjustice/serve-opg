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
      | order_form_type_2                  | both        |
      | order_form_subType                 | replacement |
      | order_form_hasAssetsAboveThreshold | no          |
    And I press "order_form_submit"
    Then the response status code should be 200
    # create deputy
    When I fill in the following:
      | deputy_appointmentType  | sole                                        |
      | deputy_deputyType       | lay                                         |
      | deputy_forename         | Dep                                         |
      | deputy_surname          | Uty                                         |
      | deputy_emailAddress     | behat-12345678-depy1@digital.justice.gov.uk |
      | deputy_contactNumber    | 38745837468347                              |
      | deputy_organisationName | org1                                        |
      | deputy_addressLine1     | Emb house                                   |
      | deputy_addressLine2     | victoria road                               |
      | deputy_addressLine3     | London                                      |
      | deputy_addressLine3     |                                             |
      | deputy_addressTown      | London                                      |
      | deputy_addressCounty    | London                                      |
      | deputy_addressCountry   | UK                                          |
      | deputy_addressPostcode  | SW1                                         |
      | deputy_deputyAnswerQ2_6 | answered-yes                                |
      | deputy_deputyS4Response | no                                          |
    And I press "deputy_saveAndContinue"



