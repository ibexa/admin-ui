@IbexaOSS @IbexaHeadless @IbexaCommerce @IbexaExperience @javascript
Feature: Content tree basic operations

  Scenario: Content tree can be displayed
    Given I open Login page in admin SiteAccess
    When I log in as admin with password publish
    When I'm on Content view Page for "root"
    Then I verify Content tree visibility

  Scenario: It is possible to display items on Content tree
    Given I create "article" Content items
      | title       | short_title   | parentPath | language |
      | Article1    | art1          | root       | eng-GB   |
      | Article2    | art2          | root       | eng-GB   |
      | Article3    | art3          | root       | eng-GB   |
    Given I open Login page in admin SiteAccess
    When I log in as admin with password publish
    When I'm on Content view Page for "root/art1"
    Then Content item "root/art1" exists in Content tree

  Scenario: New Content item can be created under chosen nested node
    Given I open Login page in admin SiteAccess
    When I log in as admin with password publish
    And I'm on Content view Page for "root/art1"
    When I start creating a new content "Article"
    And I set content fields
      | label       | value           |
      | Title       | TestArt         |
      | Short title | testart         |
      | Intro       | TestArticleIntro|
    And I perform the "Publish" action
    And I'm on Content view Page for "root/art1/testart"
    Then Content item "root/art1/testart" exists in Content tree
