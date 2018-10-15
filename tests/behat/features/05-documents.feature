Feature: documents

  Scenario: PF: add documents
    Given I log in as "behat@digital.justice.gov.uk" with password "Abcd1234"
    When I follow "order-93559316-PF"
    Then the url should match "/order/\d+/summary"
    And I should not see the "documents-cop4-actions" region

    # Add COP1A PDF
    When I click on "add-document-cop1a" in the "documents-cop1a-actions" region
    Then the url should match "/order/\d+/document/COP1A/add"
    When I attach the file "test-cop1a.pdf" to "document_form_file"
    And I click on "submit"
    Then the form should be valid
    And the order should be unservable
    And the url should match "/order/\d+/summary#documents"

    # Add COP3 PNG
    When I click on "add-document-cop3" in the "documents-cop3-actions" region
    When I attach the file "test-cop3.png" to "document_form_file"
    And I click on "submit"
    Then the form should be valid
    And the order should be unservable
    And the url should match "/order/\d+/summary#documents"

    # Add Court order
    When I click on "add-document-court_order" in the "documents-court_order-actions" region
    When I attach the file "test-court-order.jpg" to "document_form_file"
    And I click on "submit"
    Then the form should be valid
    And the order should be servable
    And the url should match "/order/\d+/summary#documents"

    # Add Other additional document
    When I click on "add-document-other" in the "documents-other" region
    Then the url should match "/order/\d+/document/OTHER/add"
    When I attach the file "test-other.jpg" to "document_form_file"
    And I click on "submit"
    Then the form should be valid
    And the url should match "/order/\d+/summary#documents"

    # Remove test-other.jpg
    When I click on "delete-documents-button" in the "documents-additional-actions" region
    Then the url should match "/order/\d+/summary"
    And I should not see the "documents-additional-filenames" region
    And the url should match "/order/\d+/summary#documents"

  Scenario: HW: add COP3, COP4, CO documents
    Given I log in as "behat@digital.justice.gov.uk" with password "Abcd1234"
    When I follow "order-93559316-HW"
    Then the url should match "/order/\d+/summary"
    And I should see the "documents-cop4-actions" region

    # Add COP3 PNG
    When I click on "add-document-cop3" in the "documents-cop3-actions" region
    When I attach the file "test-cop3.png" to "document_form_file"
    And I click on "submit"
    Then the form should be valid
    And the order should be unservable
    And the url should match "/order/\d+/summary#documents"

    # Add Court order
    When I click on "add-document-court_order" in the "documents-court_order-actions" region
    When I attach the file "test-court-order.jpg" to "document_form_file"
    And I click on "submit"
    Then the form should be valid
    And the url should match "/order/\d+/summary#documents"
    And the order should be unservable

    # Add COP4 because we dont have a PA deputy to make the order servable
    When I click on "add-document-cop4" in the "documents-cop4-actions" region
    When I attach the file "test-cop4.pdf" to "document_form_file"
    And I click on "submit"
    Then the form should be valid
    And the url should match "/order/\d+/summary#documents"
    And the order should be servable

    # Add additional document
    When I click on "add-document-other" in the "documents-other" region
    When I attach the file "test-other.jpg" to "document_form_file"
    And I click on "submit"
    Then the form should be valid
    And the url should match "/order/\d+/summary#documents"
