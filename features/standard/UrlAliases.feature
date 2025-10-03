@IbexaOSS @IbexaHeadless @IbexaExperience @IbexaCommerce @javascript @test
Feature: UrlAliases

  Background:
    Given I am logged as admin

  Scenario: Create a redirect Url Alias
      Given a "folder" Content item named "UrlAliases" exists in root
        | name       | short_name |
        | UrlAliases | UrlAliases |
      Given I'm on Content view Page for "UrlAliases"
      When I switch to "URL" tab in Content structure
      And I create a new Url Alias called "RedirectUrlAlias" in "English (United Kingdom)" language with redirect "true"
      Then there should be a "/redirecturlalias" Url Alias on the list with "Redirect" type

  Scenario: Create a direct Url Alias
    Given a "folder" Content item named "UrlAliases" exists in root
      | name       | short_name |
      | UrlAliases | UrlAliases |
    Given I'm on Content view Page for "UrlAliases"
    When I switch to "URL" tab in Content structure
    And I create a new Url Alias called "DirectUrlAlias" in "English (United Kingdom)" language with redirect "false"
    Then there should be a "/directurlalias" Url Alias on the list with "Direct" type
