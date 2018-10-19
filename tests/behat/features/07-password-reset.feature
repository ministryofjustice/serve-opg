Feature: password reset

  Scenario: Reset password

    Given I go to "/logout"
    And I go to "/login"
    And I reset the email log
    When I click on "password-reset"
    # empty form content
    When I fill in "password_reset_form_email" with ""
    And I press "password_reset_form_submit"
    Then the following fields should have an error:
      | password_reset_form_email |
    # invalid email
    When I fill in "password_reset_form_email" with "invalid"
    And I press "password_reset_form_submit"
    Then the following fields should have an error:
      | password_reset_form_email |
    # non-existing email should send no email
    When I fill in "password_reset_form_email" with "nonexistinguser@digital.justice.gov.uk"
    And I press "password_reset_form_submit"
    Then I should be on "/passwordreset/sent"
    Then there should be no email sent to "nonexistinguser@digital.justice.gov.uk"
    # valid email
    When I go to "/login"
    And I click on "password-reset"
    And I fill in "password_reset_form_email" with "behat@digital.justice.gov.uk"
    And I press "password_reset_form_submit"
    Then I should be on "/passwordreset/sent"
    # click on link
    When I click on the link in the email sent to "behat@digital.justice.gov.uk"
