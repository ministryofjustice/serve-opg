Feature: cases

  # IMPORTANT: Any new case Order numbers created or used in *any* tests, must be checked they are present in the test below
  # and cleared within the 00-init.feature (Reset Behat cases)

  Scenario: upload CSV
    Given I am authenticated with username "behat@digital.justice.gov.uk" password "Abcd1234"
    When I go to "/upload-csv"
    And I attach the file "behat-cases.csv" to "csv_upload_form_file"
    And I click on "submit"
    Then the form should be valid
    And I should see the "order-93559316-PF" region
    And I should see the "order-93559316-HW" region
    And I should see the "order-93559317-PF" region
    And I should see the "order-93559317-HW" region
    Then the response status code should be 200


  Scenario: PA order: set assets, subtype, appointment type
    Given I am authenticated with username "behat@digital.justice.gov.uk" password "Abcd1234"
    When I go to "/case"
    When I follow "order-93559316-PF"
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
      | order_form_subType                 | NEW_APPLICATION  |
      | order_form_appointmentType         | JOINT_AND_SEVERAL          |
    And I press "order_form_submit"
    Then the response status code should be 200
    And each text should be present in the corresponding region:
      | No                               | has-assets    |
      | New application                  | order-subtype |
      | Joint and several                | app-type      |
    And the order should be unservable

  Scenario: HW order: set subtype, appointment type
    Given I am authenticated with username "behat@digital.justice.gov.uk" password "Abcd1234"
    When I go to "/case"
    When I follow "order-93559316-HW"
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
      | order_form_subType         | NEW_APPLICATION |
      | order_form_appointmentType | SOLE          |
    And I press "order_form_submit"
    Then the response status code should be 200
    And I should not see the "has-assets" region
    And each text should be present in the corresponding region:
      | New application                  | order-subtype |
      | Sole                             | app-type      |
    And the order should be unservable

  Scenario: PA order: set assets, interim subtype, appointment type
    Given I am authenticated with username "behat@digital.justice.gov.uk" password "Abcd1234"
    When I go to "/case"
    When I follow "order-93559317-PF"
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
      | order_form_subType                 | INTERIM_ORDER  |
      | order_form_appointmentType         | JOINT_AND_SEVERAL          |
    And I press "order_form_submit"
    Then the response status code should be 200
    And each text should be present in the corresponding region:
      | No                               | has-assets    |
      | Interim order                    | order-subtype |
      | Joint and several                | app-type      |
    And the order should be unservable


  Scenario: HW order: set interim subtype, appointment type
    Given I am authenticated with username "behat@digital.justice.gov.uk" password "Abcd1234"
    When I go to "/case"
    When I follow "order-93559317-HW"
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
      | order_form_subType         | INTERIM_ORDER |
      | order_form_appointmentType | SOLE          |
    And I press "order_form_submit"
    Then the response status code should be 200
    And I should not see the "has-assets" region
    And each text should be present in the corresponding region:
      | Interim order                    | order-subtype |
      | Sole                             | app-type      |
    And the order should be unservable

  Scenario: test search
    Given I am authenticated with username "behat@digital.justice.gov.uk" password "Abcd1234"
    # fake q
    When I go to "/case"
    When I fill in "search" with "NOT EXISTING"
    And I press "search_submit"
    Then I should not see the "order-93559316-PF" region
    And I should not see the "order-93559316-HW" region
    # real search
    When I fill in "q" with "93559316"
    And I press "search_submit"
    # served tab has no results
    And I click on "served-tab"
    Then I should not see the "order-93559316-PF" region
    And I should not see the "order-93559316-HW" region
    # pending tab has results
    When I click on "pending-tab"
    Then I should see the "order-93559316-PF" region
    And I should see the "order-93559316-HW" region

