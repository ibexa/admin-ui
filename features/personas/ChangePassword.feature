@javascript @changePassword @IbexaOSS @IbexaHeadless @IbexaExperience @IbexaCommerce
Feature: Verify that an User allowed to change password can change his password

  Scenario: I can change my password
    Given I open Login page in admin SiteAccess
    And I log in as "UserPassword" with password "Passw0rd-42"
    When I go to user settings
    And I switch to "Account settings" tab in User settings
    And I click on the change password button
    And I change password from "Passw0rd-42" to "Passw0rd-43"
    And I perform the "Save and close" action
    Then success notification that "Your password has been successfully changed." appears
    And I should be on "User settings" page

  Scenario: I can log in with new password
    Given I open Login page in admin SiteAccess
    When I log in as "UserPassword" with password "Passw0rd-43"
    Then I should be on Dashboard page
