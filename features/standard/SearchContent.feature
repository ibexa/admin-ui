@IbexaOSS @IbexaHeadless @IbexaExperience @IbexaCommerce
Feature: Searching for a Content item
  As an administrator
  I want to search for Content items.

  @javascript @APIUser:admin
  Scenario: Content can be searched for
    Given I create "folder" Content items in root in "eng-GB"
      | name              | short_name          |
      | Searched folder   | Searched folder     |
    And I am logged as admin
    And I open "Dashboard" page in admin SiteAccess
    When I search for a Content named "Searched folder"
    Then I should see in search results an item named "Searched folder"

  @javascript @APIUser:admin
  Scenario: Content can be searched for in UDW
    Given I create "folder" Content items in root in "eng-GB"
      | name      | short_name  |
      | folderUDW | folderUDW   |
    And I am logged as admin
    And I open "Dashboard" page in admin SiteAccess
    And I open UWD from Dashboard
    When I open Search from UDW
    And I search for content item "folderUDW" through UDW
    And I select "folderUDW" item in search results through UDW
    And I edit selected content
    Then I should be on Content update page for "folderUDW"
