Feature: documents

  Scenario: PA: add documents
    Given I am logged in as behat user
    When I follow "order-12345678-pa"
    Then the url should match "/order/\d+/summary"
    # TODO: add documents

  Scenario: HW: add documents
    Given I am logged in as behat user
    When I follow "order-12345678-hw"
    Then the url should match "/order/\d+/summary"
    # TODO: add documents
