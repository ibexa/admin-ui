@IbexaOSS @IbexaHeadless @IbexaExperience @IbexaCommerce @javascript
Feature: Trash management
  As an administrator
  In order to manage content on my site
  I want to empty trash, delete, restore and restore element under new parent location in trash.

  Background:
    Given I am logged as admin

  Scenario: Trash can be emptied
    Given a "folder" Content item named "TrashTest" exists in root
      | name      | short_name |
      | TrashTest | TrashTest  |
    And I create "folder" Content items
      | name          | short_name    | parentPath     | language |
      | FolderToTrash | FolderToTrash | TrashTest | eng-GB   |
    And I send "TrashTest/FolderToTrash" to the Trash
    And I open "Trash" page in admin SiteAccess
    And trash is not empty
    When I empty the trash
    Then trash is empty

  Scenario: Content can be moved to trash
    Given a "folder" Content item named "TrashTest" exists in root
      | name      | short_name |
      | TrashTest | TrashTest  |
    And I create "folder" Content items
      | name                  | short_name            | parentPath | language |
      | FolderToTrashManually | FolderToTrashManually | TrashTest  | eng-GB   |
    And I'm on Content view Page for "TrashTest/FolderToTrashManually"
    When I send content to trash
    Then success notification that "Location 'FolderToTrashManually' moved to Trash." appears
    And I open "Trash" page in admin SiteAccess
    And there is a "Folder" "FolderToTrashManually" on Trash list

  Scenario: Element in trash can be deleted
    Given a "folder" Content item named "TrashTest" exists in root
      | name      | short_name |
      | TrashTest | TrashTest  |
    And I create "folder" Content items
      | name          | short_name    | parentPath | language |
      | DeleteFromTrash | DeleteFromTrash | TrashTest  | eng-GB   |
    And I send "TrashTest/DeleteFromTrash" to the Trash
    And I open "Trash" page in admin SiteAccess
    And there is a "Folder" "DeleteFromTrash" on Trash list
    When I delete item from trash list
      | item       |
      | DeleteFromTrash    |
    Then success notification that "Deleted selected item(s) from Trash." appears
    And there is no "Folder" "DeleteFromTrash" on Trash list

  Scenario: Element in trash can be restored
    Given a "folder" Content item named "TrashTest" exists in root
      | name      | short_name |
      | TrashTest | TrashTest  |
    And I create "folder" Content items in "TrashTest" in "eng-GB"
      | name             | short_name    |
      | RestoreFromTrash | RestoreFromTrash |
    And I send "TrashTest/RestoreFromTrash" to the Trash
    And I open "Trash" page in admin SiteAccess
    And there is a "Folder" "RestoreFromTrash" on Trash list
    When I restore item from trash
      | item             |
      | RestoreFromTrash |
    Then success notification that "Restored content to its original Location." appears
    And there is no "Folder" "RestoreFromTrash" on Trash list
    And there exists Content view Page for "TrashTest/RestoreFromTrash"

  Scenario: Element in trash can be restored under new location
    Given a "folder" Content item named "TrashTest" exists in root
      | name      | short_name |
      | TrashTest | TrashTest  |
    And I create "folder" Content items
      | name          | short_name    | parentPath | language |
      | RestoreFromTrashNewLocation | RestoreFromTrashNewLocation | TrashTest  | eng-GB   |
    And I send "TrashTest/RestoreFromTrashNewLocation" to the Trash
    And I open "Trash" page in admin SiteAccess
    And there is a "Folder" "RestoreFromTrashNewLocation" on Trash list
    When I restore item from trash under new location "Media/Files"
      | item                        |
      | RestoreFromTrashNewLocation |
    Then success notification that "Restored content under Location 'Files'." appears
    And there is no "Folder" "RestoreFromTrashNewLocation" on Trash list
    And there exists Content view Page for "Media/Files/RestoreFromTrashNewLocation"
