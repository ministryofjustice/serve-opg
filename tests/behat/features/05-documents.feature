Feature: documents

  Scenario: PA: add documents
    Given I am logged in as behat user
    When I follow "order-12345678-pa"
    Then the url should match "/order/\d+/summary"
    # Add COP1A PDF
    When I click on "add-document-cop1a" in the "documents-cop1a-actions" region
    Then the url should match "/order/\d+/document/cop1a/add"
    When I attach the file "test-cop1a.pdf" to "document_form_file"
    And I click on "submit"
    Then the form should be valid
    And the order should be unservable
#    And each text should be present in the corresponding region:
#      | good.pdf        | documents-cop1a |

    # Add COP3 PNG
    When I click on "add-document-cop3" in the "documents-cop3-actions" region
    Then the url should match "/order/\d+/document/cop3/add"
    When I attach the file "test-cop3.png" to "document_form_file"
    And I click on "submit"
    Then the form should be valid
    And the order should be unservable
#    And each text should be present in the corresponding region:
#      | test-cop3.png        | documents-cop3  |

    # Add COP4 PDF
    When I click on "add-document-cop4" in the "documents-cop4-actions" region
    Then the url should match "/order/\d+/document/cop4/add"
    When I attach the file "test-cop4.pdf" to "document_form_file"
    And I click on "submit"
    Then the form should be valid
    And the order should be unservable
#    And each text should be present in the corresponding region:
#      | test-cop4.pdf        | documents-cop4  |

    # Add Court order
    When I click on "add-document-co" in the "documents-co-actions" region
    Then the url should match "/order/\d+/document/co/add"
    When I attach the file "test-cop1c.jpg" to "document_form_file"
    And I click on "submit"
    Then the form should be valid
    And the order should be servable
#    And each text should be present in the corresponding region:
#      | test-court-order.jpg        | documents-co |

    # Add Other additional document
#    When I click on "add-document-additional" in the "documents-additional" region
#    Then the url should match "/order/\d+/document/other/add"
#    When I attach the file "test-other.jpg" to "document_form_file"
#    And I click on "submit"
#    Then the form should be valid
#    And each text should be present in the corresponding region:
#      | test-other.jpg        | documents-additional  |

    # Add COP1C JPG (not required)
    When I click on "add-document-cop1c" in the "documents-cop1c-actions" region
    Then the url should match "/order/\d+/document/cop1c/add"
    When I attach the file "test-cop1c.jpg" to "document_form_file"
    And I click on "submit"
    Then the form should be valid
#    And each text should be present in the corresponding region:
#      | test-cop1c.jpg        | documents-cop1c  |


  Scenario: HW: add COP3, COP4, CO documents
    Given I am logged in as behat user
    When I follow "order-12345678-hw"
    Then the url should match "/order/\d+/summary"
    # Add COP3 PNG
    When I click on "add-document-cop3" in the "documents-cop3-actions" region
    Then the url should match "/order/\d+/document/cop3/add"
    When I attach the file "test-cop3.png" to "document_form_file"
    And I click on "submit"
    Then the form should be valid
    And the order should be unservable
    # Add COP4 PDF
    When I click on "add-document-cop4" in the "documents-cop4-actions" region
    Then the url should match "/order/\d+/document/cop4/add"
    When I attach the file "test-cop4.pdf" to "document_form_file"
    And I click on "submit"
    Then the form should be valid
    And the order should be unservable
    # Add Court order
    When I click on "add-document-co" in the "documents-co-actions" region
    Then the url should match "/order/\d+/document/co/add"
    When I attach the file "test-cop1c.jpg" to "document_form_file"
    And I click on "submit"
    Then the form should be valid
    And the order should be servable
