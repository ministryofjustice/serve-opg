Feature: security checks

    Scenario: check what I can access when not logged
      Given I go to "/logout"
      When I go to "/case"
      Then I should be on "/login"
      When I go to "/order/1/edit"
      Then I should be on "/login"
      When I go to "/order/1/summary"
      Then I should be on "/login"
      When I go to "/order/1/declaration"
      Then I should be on "/login"
      When I go to "/order/1/deputy/add"
      Then I should be on "/login"
      When I go to "/"
      Then I should be on "/login"
