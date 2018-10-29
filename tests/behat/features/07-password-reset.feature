Feature: password reset

  Scenario: Send password link
    Given I go to "/logout"
    And I go to "/login"
    #And I reset the email log
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
    Then I should be on "/user/password-reset/request"
    And I should see "Sorry, there was a problem with the email address you entered, please try again" in the "flash" region
    # Behat user is not a live email address so unable to set up as a team member. Returns Notifcation exception.Ability: 
#    Then there should be no email sent to "nonexistinguser@digital.justice.gov.uk"
#    # valid email
#    When I go to "/login"
#    And I click on "password-reset"
#    And I fill in "password_reset_form_email" with "behat@digital.justice.gov.uk"
#    And I press "password_reset_form_submit"
#    Then I should be on "/user/password-reset/sent"

#  Scenario: Click on link, change password and login with new credentials
#    When I click on the link in the email sent to "behat@digital.justice.gov.uk"
#    # empty password
#    When I fill in the following:
#      | password_change_form_password_first   |  |
#      | password_change_form_password_second  |  |
#    And I press "password_change_form_submit"
#    Then the form should be invalid
#      #password mismatch
#    When I fill in the following:
#      | password_change_form_password_first   | Abcd1234 |
#      | password_change_form_password_second  | Abcd12345 |
#    And I press "password_change_form_submit"
#    Then the form should be invalid
#      # (nolowercase, nouppercase, no number skipped as already tested in "set password" scenario)
#      # correct !!
#    When I fill in the following:
#      | password_change_form_password_first   | Abcd12345 |
#      | password_change_form_password_second  | Abcd12345 |
#    And I press "password_change_form_submit"
#    Then the form should be valid
#    # login with old password
#    When I log in as "behat@digital.justice.gov.uk" with password "Abcd1234"
#    Then the form should be invalid
#    # login with new password
#    When I log in as "behat@digital.justice.gov.uk" with password "Abcd12345"
#    Then the form should be valid
#    And I should be on "/case"
#    # assert old activation link is not valdi anymore
#    When I click on the link in the email sent to "behat@digital.justice.gov.uk"
#    Then the response status code should be 404
