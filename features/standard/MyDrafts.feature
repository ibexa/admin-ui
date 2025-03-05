@IbexaOSS @IbexaHeadless @IbexaExperience @IbexaCommerce @javascript
Feature: My Drafts

  @test
  Scenario: It is possible to delete a draft
   Given I create "article" Content drafts
      | title       | short_title   | parentPath | language |
      | TestMyDraft | TestMyDraft   | root       | eng-GB   |
    And I am logged as admin
    And I open "MyDrafts" page in admin SiteAccess
    When I delete the draft "TestMyDraft" from my draft lists
    Then I see the draft "TestMyDraft" is deleted

  Scenario: It is possible to edit a draft
    Given I create "article" Content drafts
      | title     | short_title | parentPath | language |
      | TestMyDraft | TestMyDraft   | root       | eng-GB   |
    And I am logged as admin
    And I open "MyDrafts" page in admin SiteAccess
    When I edit "TestMyDraft" on MyDrafts page
    And I set content fields
      | label       | value                  |
      | Title       | TestMyDraftSavePublish |
      | Short title | TestMyDraftSavePublish |
      | Intro       | TestMyDraftIntro       |
    And I perform the "Save" action from the "Save and close" group
    Then I should be on Content update page for "TestMyDraftSavePublish"
