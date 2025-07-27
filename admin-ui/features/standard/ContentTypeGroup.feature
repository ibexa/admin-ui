@IbexaOSS @IbexaHeadless @IbexaExperience @IbexaCommerce
Feature: Content type groups management
  As an administrator
  In order to customize my project
  I want to manage my content type groups

  Background:
    Given I am logged as admin

  @javascript
  Scenario: Changes can be discarded while creating new content type group
    Given I open "Content type groups" page in admin SiteAccess
    When I create a new content type group
      And I set fields
        | label | value    |
        | Name  | Test content type Group |
      And I perform the "Discard" action
    Then I should be on "Content type groups" page
      And there's no "Test content type Group" content type group on content type groups list

  @javascript
  Scenario: New content type group can be added
    Given I open "Content type groups" page in admin SiteAccess
    When I create a new content type group
      And I set fields
        | label | value    |
        | Name  | Test content type Group |
      And I perform the "Save and close" action
    Then I should be on content type group page for "Test content type Group" group
    And there're no content types for that group

  @javascript
  Scenario: Changes can be discarded while editing content type group
    Given I open "Content type groups" page in admin SiteAccess
    And there's a "Test content type Group" content type group on content type groups list
    When I start editing content type group "Test content type Group"
      And I set fields
        | label | value           |
        | Name  | Test content type Group edited |
      And I perform the "Discard changes" action
    Then I should be on "Content type groups" page
      And there's a "Test content type Group" content type group on content type groups list
      And there's no "Test content type Group edited" content type group on content type groups list

  @javascript
  Scenario: Content type group can be edited
    Given I open "Content type groups" page in admin SiteAccess
    And there's a "Test content type Group" content type group on content type groups list
    When I start editing content type group "Test content type Group"
      And I set fields
        | label | value                          |
        | Name  | Test content type Group edited |
      And I perform the "Save and close" action
    Then I should be on content type group page for "Test content type Group edited" group
      And success notification that "Updated content type group 'Test content type Group'." appears

  @javascript
  Scenario: Content type group can be deleted
    Given I open "Content type groups" page in admin SiteAccess
    And there's an empty "Test content type Group edited" content type group on content type groups list
    When I delete "Test content type Group edited" from content type groups
    Then success notification that "Deleted content type group 'Test content type Group edited'." appears
      And there's no "Test content type Group edited" content type group on content type groups list

  @javascript
  Scenario: Non-empty content type group cannot be deleted
    Given I open "Content type groups" page in admin SiteAccess
    When there's non-empty "Content" content type group on content type groups list
    Then content type group "Content" cannot be selected
