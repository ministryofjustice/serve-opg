#@smoke
#Feature: prechecks
#
#    Scenario: check app status
#        Given I go to "/manage/availability"
#        Then the response status code should be 200
#
#    Scenario: check deployed versions
#        When I go to "/manage/version"
#        Then the current versions should be shown
#        And the response status code should be 200
#        And the Content-Type response header should be application/json
#
#    @excludeLocal
#    Scenario: check application environment is running prod mode
#        When I go to "/manage/app-env"
#        Then I should see "prod"
