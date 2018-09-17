Feature: serve order

  Scenario: Serve PA order
    Given I am logged in as behat user
    When I follow "order-12345678-pa"
    # summary page
    Then the url should match "order/\d+/summary"
    When I follow "serve_order_button"
    # declaration page
    Then the url should match "order/\d+/declaration"
    When I press "declaration_form_submit"
    # case list page: assert order is in "pending tab"
    Then I should be on "/case"
    And I should not see the "order-12345678-pa" region
    When I click on "served-tab"
    Then I should see the "order-12345678-pa" region
    And I should see "test-cop1a.pdf" in the "order-12345678-pa" region

  Scenario: Serve HW order
    Given I am logged in as behat user
    When I follow "order-12345678-hw"
    # summary page
    Then the url should match "order/\d+/summary"
    When I follow "serve_order_button"
    # declaration page
    Then the url should match "order/\d+/declaration"
    When I press "declaration_form_submit"
    # case list page: assert order is in "pending tab"
    Then I should be on "/case"
    And I should not see the "order-12345678-hw" region
    When I click on "served-tab"
    Then I should see the "order-12345678-hw" region
    And I should see "test-other.jpg" in the "order-12345678-hw" region
