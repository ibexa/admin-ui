@IbexaOSS @IbexaHeadless @IbexaExperience @IbexaCommerce @contentTypeFields
Feature: Content fields setting and editing
  As an administrator
  In order to manage content on my site
  I want to set, edit, copy and move content items.

  @javascript @APIUser:admin @contentQuery
  Scenario Outline: Create content item with Content Query field
    Given I create a "<fieldName> CT" content type in "Content" with "<fieldInternalName>" identifier
      | Field Type  | Name        | Identifier          | Required | Searchable | Translatable | Settings        |
      | <fieldName> | Field       | <fieldInternalName> | no       | no	        | yes          | <fieldSettings> |
      | Text line   | Name        | name	            | no       | yes	    | yes          |                 |
    And a "folder" Content item named "ContentQueryFieldContainer" exists in root
      | name                       | short_name                 |
      | ContentQueryFieldContainer | ContentQueryFieldContainer |
    Given I am logged as admin
    And I'm on Content view Page for "ContentQueryFieldContainer"
    When I start creating a new content "<fieldName> CT"
    And the "ibexa_content_query" field is noneditable
    And I set content fields
      | label    | <label1>    |
      | Name     | <fieldName> |
    And I perform the "Publish" action
    Then success notification that "Content published." appears
    And I should be on Content view Page for "ContentQueryFieldContainer/<fieldName>"
    And content attributes equal
      | label    | <label1> | fieldTypeIdentifier   |
      | Field    | <value1> | <fieldInternalName> |
    Examples:
      | fieldInternalName | fieldName     | fieldSettings                                                                                                  | label1 | value1                  |
      | ibexa_content_query    | Content query | QueryType-Folders under media,ContentType-folder,ItemsPerPage-100,Parameters-contentTypeId:folder;locationId:43| value  | Images,Files,Multimedia |

  @javascript @APIUser:admin @contentQuery
  Scenario: Edit content item with Content Query
    Given I am logged as admin
    And I'm on Content view Page for "ContentQueryFieldContainer/Content query"
    When I perform the "Edit" action
    And I set content fields
      | label    | <label1>          |
      | Name     | New Content query |
    And I perform the "Publish" action
    Then success notification that "Content published." appears
    And I should be on Content view Page for "ContentQueryFieldContainer/New Content query"
    And content attributes equal
      | label    | value                   | fieldTypeIdentifier |
      | Field    | Images,Files,Multimedia | ibexa_content_query      |
