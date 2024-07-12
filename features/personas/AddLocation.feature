@javascript @addLocation
Feature: Verify that an Editor with content type limitation on content/create policy can add location

  @IbexaOSS @IbexaHeadless @IbexaExperience @IbexaCommerce @test1
  Scenario: I can add location
    Given I open Login page in admin siteaccess
    And I log in as "Add" with password "Passw0rd-42"
    And I am using the DXP with Focus mode disabled
    And I reload the page
    And I go to "Content structure" in "Content" tab
    And I navigate to content "NewArticle" of type "Article" in root
    When I switch to "Locations" tab in Content structure
    And I add a new Location under "root/Destination"
    Then there exists Content view Page for "root/Destination/NewArticle"
