Feature: serve order

  Scenario: Serve PA order
    Given I am logged in as behat user
    When I follow "order-93559316-PF"
    # summary page
    Then the url should match "order/\d+/summary"
    When I follow "serve_order_button"
    # declaration page
    Then the url should match "order/\d+/declaration"
    When I press "declaration_form_submit"
    # case list page: assert order is in "pending tab"
    Then I should be on "/case"
    And I should not see the "order-93559316-PF" region
    When I click on "served-tab"
    Then I should see the "order-93559316-PF" region
    And I should see "test-cop1a.pdf" in the "order-93559316-PF" region

  Scenario: Serve HW order
    Given I am logged in as behat user
    When I follow "order-93559316-HW"
    # summary page
    Then the url should match "order/\d+/summary"
    When I follow "serve_order_button"
    # declaration page
    Then the url should match "order/\d+/declaration"
    When I press "declaration_form_submit"
    # case list page: assert order is in "pending tab"
    Then I should be on "/case"
    And I should not see the "order-93559316-HW" region
    When I click on "served-tab"
    Then I should see the "order-93559316-HW" region
    And I should see "test-other.jpg" in the "order-93559316-HW" region
