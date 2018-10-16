Feature: password reset

  Scenario: Reset password

    Given go to "/logout"
    And I go to "/login"
    When I click on "password-reset"
    # empty form content
    When I fill in "password_reset_form_email" with ""
    And I press "password_reset_form_submit"
    Then the following fields should have an error:
     |password_reset_form_email |
    # invalid email
    When I fill in "password_reset_form_email" with "invalid"
    And I press "password_reset_form_submit"
    Then the following fields should have an error:
      |password_reset_form_email |
    # non-existing email
    When I fill in "password_reset_form_email" with "nonexistinguser@digital.justice.gov.uk"
    And I press "password_reset_form_submit"
    Then I should be on "/password-reset/sent"
    #Then I should not see an email sent to "nonexistinguser@digital.justice.gov.uk"
    # valid email
    When I go to "/password-reset/request"
    And I fill in "password_reset_form_email" with "behat@digital.justice.gov.uk"
    And I press "password_reset_form_submit"
    Then I should be on "/password-reset/sent"
    #Then I should not see an email sent to "behat@digital.justice.gov.uk"
    #
