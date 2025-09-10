@IbexaHeadless @IbexaExperience @IbexaCommerce @javascript
Feature: Content items creation
  As an administrator
  In order to manage content to my site
  I want to create, edit, copy and move content items.
  
Background:
      Given I am logged as admin

@IbexaOSS
Scenario: Content moving can be cancelled
  Given a "folder" Content item named "ContentManagement" exists in root
      | name              | short_name        |
      | ContentManagement | ContentManagement |
  And I create "folder" Content items
    | name               | short_name          | parentPath        | language |
    | FolderToCancelMove | FolderToCancelMove  | ContentManagement | eng-GB   |
  And I'm on Content view Page for "ContentManagement/FolderToCancelMove"
  When I perform the "Move" action
    And I select content "Media" through UDW
    And I close the UDW window
  Then I should be on Content view Page for "ContentManagement/FolderToCancelMove"

@IbexaOSS
Scenario: Content can be moved
  Given a "folder" Content item named "ContentManagement" exists in root
      | name              | short_name        |
      | ContentManagement | ContentManagement |
  And I create "folder" Content items
    | name               | short_name        | parentPath        | language |
    | FolderToMove       | FolderToMove      | ContentManagement | eng-GB   |
  And I'm on Content view Page for "ContentManagement/FolderToMove"
  When I perform the "Move" action
    And I select content "Media/Files" through UDW
    And I confirm the selection in UDW
  Then success notification that "'FolderToMove' moved to 'Files'" appears
    And I should be on Content view Page for "Media/Files/FolderToMove"
    And I'm on Content view Page for "ContentManagement"
    And there's no "FolderToMove" "Folder" on Subitems list

@IbexaOSS
Scenario: Content copying can be cancelled
  Given a "folder" Content item named "ContentManagement" exists in root
      | name              | short_name        |
      | ContentManagement | ContentManagement |
  And I create "folder" Content items
    | name               | short_name         | parentPath        | language |
    | FolderToCopyCancel | FolderToCopyCancel | ContentManagement | eng-GB   |
  And I'm on Content view Page for "ContentManagement/FolderToCopyCancel"
  When I perform the "Copy" action
    And I select content "Media" through UDW
    And I close the UDW window
  Then I should be on Content view Page for "ContentManagement/FolderToCopyCancel"

@IbexaOSS
Scenario: Content can be copied
  Given a "folder" Content item named "ContentManagement" exists in root
      | name              | short_name        |
      | ContentManagement | ContentManagement |
  And I create "folder" Content items
    | name               | short_name         | parentPath        | language |
    | FolderToCopy       | FolderToCopy       | ContentManagement | eng-GB   |
  And I'm on Content view Page for "ContentManagement/FolderToCopy"
  When I perform the "Copy" action
  And I select content "Media/Files" through UDW
    And I confirm the selection in UDW
  Then success notification that "'FolderToCopy' copied to 'Files'" appears
    And I should be on Content view Page for "Media/Files/FolderToCopy"
    And I'm on Content view Page for "ContentManagement"
    And there's a "FolderToCopy" "Folder" on Subitems list

  @IbexaOSS
  Scenario: Subtree copying can be cancelled
  Given a "folder" Content item named "ContentManagement" exists in root
      | name              | short_name        |
      | ContentManagement | ContentManagement |
  And I create "folder" Content items
      | name                      | short_name                | parentPath        | language |
      | FolderToSubtreeCopyCancel | FolderToSubtreeCopyCancel | ContentManagement | eng-GB   |
    And I'm on Content view Page for "ContentManagement/FolderToSubtreeCopyCancel"
    When I perform the "Copy Subtree" action
    And I select content "Media" through UDW
    And I close the UDW window
    Then I should be on Content view Page for "ContentManagement/FolderToSubtreeCopyCancel"

  @IbexaOSS
  Scenario: Subtree can be copied
    Given a "folder" Content item named "ContentManagement" exists in root
      | name              | short_name        |
      | ContentManagement | ContentManagement |
    And I create "folder" Content items
      | name                      | short_name                | parentPath        | language |
      | FolderToSubtreeCopy | FolderToSubtreeCopy | ContentManagement | eng-GB   |
    And I'm on Content view Page for "ContentManagement/FolderToSubtreeCopy"
    When I perform the "Copy Subtree" action
    And I select content "Media" through UDW
    And I confirm the selection in UDW
    Then success notification that "Subtree 'FolderToSubtreeCopy' copied to Location 'Media'" appears
    And I should be on Content view Page for "Media/FolderToSubtreeCopy"
    And I'm on Content view Page for "ContentManagement"
    And there's a "FolderToSubtreeCopy" "Folder" on Subitems list

  @IbexaOSS
  Scenario: Content can be hidden now
    Given I'm on Content view Page for root
    When I start creating a new content "Article"
    And I set content fields
      | label       | value                |
      | Title       | Test Article to hide |
      | Short title | Test Article to hide |
      | Intro       | TestArticleIntro     |
    And I perform the "Publish" action
    Then success notification that "Content published." appears
    And I should be on Content view Page for "Test Article to hide"
    When I perform the "Hide" action
    And I should be on Content view Page for "Test Article to hide"
    Then I should see alert "This Content item or its Location is hidden." appears

  Scenario: Content can be hidden now
    Given I'm on Content view Page for root
    When I start creating a new content "Article"
    And I set content fields
      | label       | value                |
      | Title       | Test Article to hide |
      | Short title | Test Article to hide |
      | Intro       | TestArticleIntro     |
    And I perform the "Publish" action
    Then success notification that "Content published." appears
    And I should be on Content view Page for "Test Article to hide"
    When I perform the "Hide" action
    And I perform the "Confirm" action
    And I should be on Content view Page for "Test Article to hide"
    Then I should see alert "This Content item or its Location is hidden." appears

  @IbexaOSS
  Scenario: Hidden content can be reveal
    Given I'm on Content view Page for root
    And I navigate to content "Test Article to hide" of type "Article" in root
    When I perform the "Reveal" action
    And I should be on Content view Page for "Test Article to hide"
    Then success notification that "Content item 'Test Article to hide' revealed." appears
