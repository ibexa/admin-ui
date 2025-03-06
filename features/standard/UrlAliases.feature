@IbexaOSS @IbexaHeadless @IbexaExperience @IbexaCommerce @javascript
Feature: UrlAliases

  Background:
    Given I am logged as admin

    @urlalias
  Scenario: Url Alias can be created
      Given a "folder" Content item named "UrlAlias2" exists in root
        | name      | short_name |
        | UrlAlias2 | UrlAlias2  |
      And I'm on Content view Page for "root/UrlAlias2"
      And I switch to "URL" tab in Content structure
      When I create a new direct Url Alias called "TestUrlAlias" in "English (United Kingdom)" laguage
      Then there should be a "/testurlalias" Url Alias in the list with "Redirect" type