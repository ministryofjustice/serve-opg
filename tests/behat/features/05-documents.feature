Feature: documents

  Scenario: PA: add documents
    Given I log in as "behat@digital.justice.gov.uk" with password "Abcd1234"
    When I follow "order-12345678-pa"
    Then the url should match "/order/\d+/summary"

    # Add COP1A PDF
    When I click on "add-document-cop1a" in the "documents-cop1a-actions" region
    Then the url should match "/order/\d+/document/cop1a/add"
    When I attach the file "test-cop1a.pdf" to "document_form_file"
    And I click on "submit"
    Then the form should be valid
    And the order should be unservable

    # Add COP3 PNG
    When I click on "add-document-cop3" in the "documents-cop3-actions" region
    When I attach the file "test-cop3.png" to "document_form_file"
    And I click on "submit"
    Then the form should be valid
    And the order should be unservable

    # Add COP4 PDF
    When I click on "add-document-cop4" in the "documents-cop4-actions" region
    When I attach the file "test-cop4.pdf" to "document_form_file"
    And I click on "submit"
    Then the form should be valid
    And the order should be unservable

    # Add Court order
    When I click on "add-document-co" in the "documents-co-actions" region
    When I attach the file "test-court-order.jpg" to "document_form_file"
    And I click on "submit"
    Then the form should be valid
    And the order should be servable

    # Add Other additional document
    When I click on "add-document-additional" in the "documents-additional" region
    Then the url should match "/order/\d+/document/additional/add"
    When I attach the file "test-other.jpg" to "document_form_file"
    And I click on "submit"
    Then the form should be valid

    # Add COP1C JPG (not required)
    When I click on "add-document-cop1c" in the "documents-cop1c-actions" region
    When I attach the file "test-cop1c.jpg" to "document_form_file"
    And I click on "submit"
    Then the form should be valid
    And each text should be present in the corresponding region:
      | test-cop1a.pdf        | document-cop1a-filename |
      | test-cop1c.jpg        | document-cop1c-filename |
      | test-cop3.png         | document-cop3-filename  |
      | test-cop4.pdf         | document-cop4-filename  |
      | test-court-order.jpg  | document-co-filename |
      | test-other.jpg        | documents-additional-filenames  |

    # Remove test-other.jpg
    When I click on "delete-documents-button" in the "documents-additional-actions" region
    Then the url should match "/order/\d+/summary"
    And I should not see the "documents-additional-filenames" region

  Scenario: HW: add COP3, COP4, CO documents
    Given I log in as "behat@digital.justice.gov.uk" with password "Abcd1234"
    When I follow "order-12345678-hw"
    Then the url should match "/order/\d+/summary"
    # Add COP3 PNG
    When I click on "add-document-cop3" in the "documents-cop3-actions" region
    When I attach the file "test-cop3.png" to "document_form_file"
    And I click on "submit"
    Then the form should be valid
    And the order should be unservable
    # Add COP4 PDF
    When I click on "add-document-cop4" in the "documents-cop4-actions" region
    When I attach the file "test-cop4.pdf" to "document_form_file"
    And I click on "submit"
    Then the form should be valid
    And the order should be unservable
    # Add Court order
    When I click on "add-document-co" in the "documents-co-actions" region
    When I attach the file "test-cop1c.jpg" to "document_form_file"
    And I click on "submit"
    Then the form should be valid
    And the order should be servable
    # Add additional docuiment
    # Add Other additional document
    When I click on "add-document-additional" in the "documents-additional" region
    When I attach the file "test-other.jpg" to "document_form_file"
    And I click on "submit"
    Then the form should be valid
