@IbexaOSS @IbexaHeadless @IbexaExperience @IbexaCommerce @javascript
Feature: UrlAliases

  Background:
    Given I am logged as admin

    @test2
  Scenario: Create an Url Alias
    Given a "folder" Content item named "UrlAliases" exists in root
      | name       | short_name |
      | UrlAliases | UrlAliases |
    Given I'm on Content view Page for "UrlAliases"
    When I switch to "URL" tab in Content structure
    And I create a new direct Url Alias called "TestUrlAlias" in "English (United Kingdom)" language
    Then there should be a "/testurlalias" Url Alias in the list with "Direct" type

  Scenario: Delete an Url Alias
