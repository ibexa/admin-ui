@IbexaOSS @IbexaHeadless @IbexaCommerce @IbexaExperience @javascript

Feature: Content tree basic operations
  Scenario: Content tree can be displayed
#    Given I am logged as admin
    Given I open Login page in admin SiteAccess
    And I log in as admin with password publish
    And I should be on Dashboard page
    When I go to "Content structure" in Content tab
    Then I verify Content tree visibility

  Scenario: It is possible to display items on Content tree
    Given I create "article" Content items
      | title       | short_title   | parentPath | language |
      | Article1    | art1          | root       | eng-GB   |
      | Article2    | art2          | root       | eng-GB   |
      | Article3    | art3          | root       | eng-GB   |
    And I am logged as admin
    And I'm on Content view Page for "root/art1"

  Scenario: New Content item can be created under chosen nested node
    Given I am logged as admin
    And I'm on Content view Page for "root/art1"
    When I start creating a new content "Article"
    And I set content fields
      | label       | value                  |
      | Title       | Arttest |
      | Short title | arttest |
      | Intro       | TestArticleIntro       |
    And I perform the "Publish" action
    And I should be on Content view Page for "root/art1/arttest"