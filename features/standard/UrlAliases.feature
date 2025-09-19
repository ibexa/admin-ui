@IbexaOSS @IbexaHeadless @IbexaExperience @IbexaCommerce @javascript
Feature: UrlAliases

  Background:
    Given I am logged as admin

    @test
  Scenario: Create a redirect Url Alias
      Given a "folder" Content item named "UrlAliases" exists in root
        | name       | short_name |
        | UrlAliases | UrlAliases |
      Given I'm on Content view Page for "UrlAliases"
      When I switch to "URL" tab in Content structure
      And I create a new redirect Url Alias called "RedirectUrlAlias" in "English (United Kingdom)" language
      Then there should be a "/redirecturlalias" Url Alias on the list with "Redirect" type

    @test2
  Scenario: Create a direct Url Alias
    Given a "folder" Content item named "UrlAliases" exists in root
      | name       | short_name |
      | UrlAliases | UrlAliases |
    Given I'm on Content view Page for "UrlAliases"
    When I switch to "URL" tab in Content structure
    And I create a new direct Url Alias called "DirectUrlAlias" in "English (United Kingdom)" language
    Then there should be a "/directurlalias" Url Alias on the list with "Direct" type