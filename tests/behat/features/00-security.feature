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

    Scenario: after 5 attempts to login with the wrong password, I'm locked
      When I go to "/behat/reset-brute-force-attempts-logger"
      When I go to "/logout"
      When I log in as "behat@digital.justice.gov.uk" with password "wrong password"
      When I log in as "behat@digital.justice.gov.uk" with password "wrong password"
      When I log in as "behat@digital.justice.gov.uk" with password "wrong password"
      When I log in as "behat@digital.justice.gov.uk" with password "wrong password"
      When I log in as "behat@digital.justice.gov.uk" with password "wrong password"
      # ENABLE when implemented
#      Then I should be on "/login"
#      And I should see "locked" in the "form-errors" region
      And I go to "/behat/reset-brute-force-attempts-logger"

