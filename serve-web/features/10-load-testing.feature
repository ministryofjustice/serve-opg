Feature: Load testing

  Scenario: Uploading a CSV with 2499 rows does not time out
    Given I log in as "behat@digital.justice.gov.uk" with correct password
    When I go to "/upload-csv"
    And I attach the file "behat-cases-large.csv" to "csv_upload_form_file"
    And I click on "submit"
    Then the form should be valid
    Then the response status code should be 200

  Scenario: Uploading an XLSX with 2499 rows does not time out
    Given I log in as "behat@digital.justice.gov.uk" with correct password
    When I go to "/upload-csv"
    And I attach the file "behat-cases-large.xlsx" to "csv_upload_form_file"
    And I click on "submit"
    Then the form should be valid
    Then the response status code should be 200
