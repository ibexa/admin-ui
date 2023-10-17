@IbexaOSS @IbexaHeadless @IbexaExperience @IbexaCommerce @javascript
Feature: Content items creation
  As an administrator
  In order to manage content to my site
  I want to create, edit, copy and move content items.
  
Background:
      Given I am logged as admin

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
