Feature: serve order
  
  Scenario: Serve PF order
    Given I log in as "behat@digital.justice.gov.uk" with correct password
    When I go to "/case"
    When I follow "order-93559316-PF"
    # summary page
    Then the url should match "order/\d+/summary"
    When I follow "serve_order_button"
    # declaration page
    Then the url should match "order/\d+/declaration"
    When I press "declaration_form_submit"
    Then I should be on "/case"
    And I should see "Order served to OPG" in the "flash" region
    And I should see "93559316" in the "flash" region
    # case list page: assert order is in "pending tab"
    And I should not see the "93559316-PF" region
    When I click on "served-tab"
    Then I should see the "order-93559316-PF" region
    And I should see "test-cop1a.pdf" in the "order-93559316-PF" region
    And the documents for order "93559316-PF" should be transferred

  Scenario: Serve HW order
    Given I log in as "behat@digital.justice.gov.uk" with correct password
    When I go to "/case"
    When I follow "order-93559316-HW"
    # summary page
    Then the url should match "order/\d+/summary"
    When I follow "serve_order_button"
    # declaration page
    Then the url should match "order/\d+/declaration"
    When I press "declaration_form_submit"
    Then I should be on "/case"
    # case list page: assert order is in "pending tab"
    And I should not see the "order-93559316-HW" region
    And I should see "Order served to OPG" in the "flash" region
    And I should see "93559316" in the "flash" region
    When I click on "served-tab"
    Then I should see the "order-93559316-HW" region
    And I should see "supported1.docx" in the "order-93559316-HW" region
    And I should see "supported3.tiff" in the "order-93559316-HW" region
    And the documents for order "93559316-HW" should be transferred
