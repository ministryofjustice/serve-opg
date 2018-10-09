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

    Scenario: after 5 attempts to login with the wrong password, I'm locked for 10 minutes
      Given I go to "/behat/reset-brute-force-attempts-logger"
      And I go to "/logout"
      When I log in as "behat@digital.justice.gov.uk" with password "wrong password"
      Then I should see "Invalid credentials" in the "form-errors" region
      When I log in as "behat@digital.justice.gov.uk" with password "wrong password"
      Then I should see "Invalid credentials" in the "form-errors" region
      When I log in as "behat@digital.justice.gov.uk" with password "wrong password"
      Then I should see "Invalid credentials" in the "form-errors" region
      When I log in as "behat@digital.justice.gov.uk" with password "wrong password"
      Then I should see "Invalid credentials" in the "form-errors" region
      When I log in as "behat@digital.justice.gov.uk" with password "wrong password"
      Then I should see "Invalid credentials" in the "form-errors" region
      When I log in as "behat@digital.justice.gov.uk" with password "wrong password"
      Then I should see the "form-errors" region
      But I should not see "Invalid credentials" in the "form-errors" region
      #And I should see "locked" in the "form-errors" region
      # reset attempts
      When I go to "/behat/reset-brute-force-attempts-logger"
      And I log in as "behat@digital.justice.gov.uk" with password "wrong password"
      Then I should see "Invalid credentials" in the "form-errors" region

