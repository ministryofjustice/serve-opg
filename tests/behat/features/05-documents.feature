Feature: documents

  Scenario: PA: add documents
    Given I am logged in as behat user
    When I follow "order-12345678-pa"
    Then the url should match "/order/\d+/summary"
#    # Add COP1A PDF
#    When I click on "Add document" in the "documents-cop-1a" region
#    Then the url should match "/order/\d+/document/cop1a/add"
#    When I attach the file "test-cop1a.pdf" to "report_document_upload_file"
#    And I click on "attach-file"
#    Then the form should be valid
#    And each text should be present in the corresponding region:
#      | good.pdf        | documents-cop-1a |
#
#
#    # Add COP1C JPG
#    When I click on "Add document" in the "documents-cop-1c" region
#    Then the url should match "/order/\d+/document/cop1c/add"
#    When I attach the file "test-cop1c.jpg" to "report_document_upload_file"
#    And I click on "attach-file"
#    Then the form should be valid
#    And each text should be present in the corresponding region:
#      | test-cop1c.jpg        | documents-cop-1c  |
#
#    # Add COP3 PNG
#    When I click on "Add document" in the "documents-cop-3" region
#    Then the url should match "/order/\d+/document/cop3/add"
#    When I attach the file "test-cop3.png" to "report_document_upload_file"
#    And I click on "attach-file"
#    Then the form should be valid
#    And each text should be present in the corresponding region:
#      | test-cop3.png        | documents-cop-3  |
#
#    # Add COP4 PDF
#    When I click on "Add document" in the "documents-cop-4" region
#    Then the url should match "/order/\d+/document/cop4/add"
#    When I attach the file "test-cop4.pdf" to "report_document_upload_file"
#    And I click on "attach-file"
#    Then the form should be valid
#    And each text should be present in the corresponding region:
#      | test-cop4.pdf        | documents-cop-4  |
#
#    # Add Court order
#    When I click on "Add document" in the "documents-court-order" region
#    Then the url should match "/order/\d+/document/court-order/add"
#    When I attach the file "test-cop1c.jpg" to "report_document_upload_file"
#    And I click on "attach-file"
#    Then the form should be valid
#    And each text should be present in the corresponding region:
#      | test-court-order.jpg        | documents-court-order  |
#
#    # Add Other additional document
#    When I click on "Add additional document" in the "documents-additional" region
#    Then the url should match "/order/\d+/document/other/add"
#    When I attach the file "test-other.jpg" to "report_document_upload_file"
#    And I click on "attach-file"
#    Then the form should be valid
#    And each text should be present in the corresponding region:
#      | test-other.jpg        | documents-additional  |

  Scenario: HW: add documents
    Given I am logged in as behat user
    When I follow "order-12345678-hw"
    Then the url should match "/order/\d+/summary"
    # TODO: add documents
