@IbexaExperience
Feature: Searching for existing item into Back Office.
  As an administrator
  I want to search whichever existing element using global search feature.

  @javascript @APIUser:admin
  Scenario: Content can be searched for
    Given I create "folder" Content items in root in "eng-GB"
      | name              | short_name          |
      | GlobalSearchTest folder   | GlobalSearchTest folder     |
    Given I open Login page in admin SiteAccess
    And I am logged as admin
    And I open "Search" page in admin Dashboard
    When I search for a Content named "GlobalSearchTest folder"
    Then I should see in search results an item named "GlobalSearchTest folder"
