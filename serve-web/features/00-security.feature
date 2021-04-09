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
      Given I go to "/logout"
      And I go to "/behat/reset-brute-force-attempts-logger"
      And the response status code should be 200
      When I log in as "behat@digital.justice.gov.uk" with wrong password
      Then I should see the "form-errors" region
      When I log in as "behat@digital.justice.gov.uk" with wrong password
      Then I should see the "form-errors" region
      When I log in as "behat@digital.justice.gov.uk" with wrong password
      Then I should see the "form-errors" region
      When I log in as "behat@digital.justice.gov.uk" with wrong password
      Then I should see the "form-errors" region
      When I log in as "behat@digital.justice.gov.uk" with wrong password
      Then I should see the "form-locked-error" region
      # reset attempts and confirm that the error is now due to erorrs
      When I go to "/logout"
      And I go to "/behat/reset-brute-force-attempts-logger"
      And the response status code should be 200
      And I log in as "behat@digital.justice.gov.uk" with wrong password
      Then I should not see the "form-locked-error" region

    Scenario: Login Autocomplete disabled
      Given I go to "/login"
      When the response status code should be 200
      Then auto complete should be disabled
