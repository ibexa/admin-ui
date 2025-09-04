@IbexaOSS @IbexaHeadless @IbexaExperience @IbexaCommerce @javascript @setup
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

  Scenario: Element in trash can be found by search
    Given a "folder" Content item named "TrashTest" exists in root
        | name      | short_name |
        | TrashTest | TrashTest  |
    And I create "folder" Content items in "TrashTest" in "eng-GB"
        | name             | short_name    |
        | TrashSearch1 | TrashSearch1 |
        | TrashSearch2 | TrashSearch2 |
    And I send "TrashTest/TrashSearch1" to the Trash
    And I send "TrashTest/TrashSearch2" to the Trash
    And I open "Trash" page in admin SiteAccess
    Then I search for a "TrashSearch1" content item in Trash
    And there is a "Folder" "TrashSearch1" on Trash list

  Scenario: Element in trash can be found by search and filtered by content type
    Given a "folder" Content item named "TrashTest" exists in root
      | name      | short_name |
      | TrashTest | TrashTest  |
    And I create "article" Content items in "TrashTest" in "eng-GB"
      | title        | short_title    |
      | TrashSearch3 | TrashSearch3 |
    And I send "TrashTest/TrashSearch3" to the Trash
    And I open "Trash" page in admin SiteAccess
    Then I filter search by "Article" content type
    And I confirm search in Trash
    And there is a "Article" "TrashSearch3" on Trash list

  Scenario: Element in trash can be found by search and filtered by section
    Given I create "folder" Content items in "Media/Files" in "eng-GB"
      | name        | short_name    |
      | TrashSearch4 | TrashSearch4 |
    And I send "Media/Files/TrashSearch4" to the Trash
    And I open "Trash" page in admin SiteAccess
    Then I filter search by "Media" section
    And I confirm search in Trash
    And there is a "Folder" "TrashSearch4" on Trash list

  Scenario: Element in trash can be found by search and filtered by content item creator
    Given I execute a migration
    """
    - type: user
      mode: create
      metadata:
        login: trash_admin
        email: trash_admin@link.invalid
        password: Passw0rd-42
        enabled: true
        mainLanguage: eng-GB
        contentType: user
      groups:
        - 9b47a45624b023b1a76c73b74d704acf
      fields:
        - fieldDefIdentifier: first_name
          languageCode: eng-GB
          value: TrashAdmin
        - fieldDefIdentifier: last_name
          languageCode: eng-GB
          value: User
        - fieldDefIdentifier: signature
          languageCode: eng-GB
          value: null
        - fieldDefIdentifier: image
          languageCode: eng-GB
          value: null
      references:
        -
          name: test_user_id
          type: user_id
    - type: content
      mode: create
      metadata:
        contentType: article
        mainTranslation: eng-GB
        creatorId: reference:test_user_id
        alwaysAvailable: false
      location:
        parentLocationId: 2
        hidden: false
        sortField: 1
        sortOrder: 1
        priority: 0
      fields:
        - fieldDefIdentifier: title
          languageCode: eng-GB
          value:  "trash_article"
        - fieldDefIdentifier: short_title
          languageCode: eng-GB
          value: "test_trash_article"
        - fieldDefIdentifier: author
          languageCode: eng-GB
          value:
            - id: '1'
              name: 'Administrator User'
              email: admin@link.invalid
        - fieldDefIdentifier: intro
          languageCode: eng-GB
          value:
            xml: |
              <?xml version="1.0" encoding="UTF-8"?>
              <section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ibexa.co/xmlns/dxp/docbook/xhtml" xmlns:ezcustom="http://ibexa.co/xmlns/dxp/docbook/custom" version="5.0-variant ezpublish-1.0"><para>This is an example intro</para></section>
        - fieldDefIdentifier: body
          languageCode: eng-GB
          value:
            xml: |
              <?xml version="1.0" encoding="UTF-8"?>
              <section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ibexa.co/xmlns/dxp/docbook/xhtml" xmlns:ezcustom="http://ibexa.co/xmlns/dxp/docbook/custom" version="5.0-variant ezpublish-1.0"><para>This is the main article content</para></section>
        - fieldDefIdentifier: enable_comments
          languageCode: eng-GB
          value: false
        - fieldDefIdentifier: image
          languageCode: eng-GB
          value:
            destinationContentId: null
      references:
        -
          name: activity_article_id
          type: content_id
    """
    And I send "root/test_trash_article" to the Trash
    And I open "Trash" page in admin SiteAccess
    Then I filter search by "TrashAdmin User" content item creator
    And I confirm search in Trash
    And there is a "Article" "test_trash_article" on Trash list