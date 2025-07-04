@IbexaOSS @IbexaHeadless @IbexaExperience @IbexaCommerce @contentTypeFields
Feature: Content fields setting and editing
  As an administrator
  In order to manage content on my site
  I want to set, edit, copy and move content items.

  @javascript @APIUser:admin
  Scenario Outline: Create content item with given field
    Given I create a "<fieldName> CT" content type in "Content" with "<fieldInternalName>" identifier
      | Field Type  | Name        | Identifier          | Required | Searchable | Translatable | Settings       |
      | <fieldName> | Field       | <fieldInternalName> | no      | no	      | yes          | <fieldSettings>  |
      | Text line   | Name        | name	            | no      | yes	      | yes          |                  |
      And a "folder" Content item named "TextFieldsContainer" exists in root
      | name                | short_name          |
      | TextFieldsContainer | TextFieldsContainer |
      And I am logged as admin
      And I'm on Content view Page for TextFieldsContainer
    When I start creating a new content "<fieldName> CT"
      And I set content fields
        | label    | <label1>    | <label2> | <label3> |
        | Field    | <value1>    | <value2> | <value3> |
        | Name     | <fieldName> |          |          |
      And I perform the "Publish" action
    Then success notification that "Content published." appears
      And I should be on Content view Page for "TextFieldsContainer/<contentItemName>"
      And content attributes equal
          | label    | <label1> | <label2> | <label3> |
          | Field    | <value1> | <value2> | <value3> |

    Examples:
      | fieldInternalName    | fieldName                    | fieldSettings                                                         |  label1   | value1                                                                    | label2     | value2                | label3  | value3      | contentItemName           |
      | ibexa_string             | Text line                    |                                                                       | value     | Lorem ipsum                                                               |            |                       |         |             | Lorem ipsum               |
      | ibexa_author             | Authors                      |                                                                       | name      | Test Name                                                                 | email      | email@example.com     |         |             | Test Name                 |
      | ibexa_richtext           | Rich text                    |                                                                       | value     | Lorem ipsum dolor sit                                                     |            |                       |         |             | Lorem ipsum dolor sit     |
      | ibexa_text               | Text block                   |                                                                       | value     | Lorem ipsum dolor                                                         |            |                       |         |             | Lorem ipsum dolor         |
      | ibexa_url                | URL                          |                                                                       | text      | Test URL                                                                  | url        | http://www.google.com |         |             | Test URL                  |

  @javascript @APIUser:admin
  Scenario Outline: Edit content item with given field
    Given I am logged as admin
      And I'm on Content view Page for "TextFieldsContainer/<oldContentItemName>"
    When I perform the "Edit" action
      And I set content fields
        | label    | <label1> | <label2> | <label3> |
        | Field    | <value1> | <value2> | <value3> |
      And I perform the "Publish" action
    Then success notification that "Content published." appears
      And I should be on Content view Page for "TextFieldsContainer/<newContentItemName>"
      And content attributes equal
        | label    | <label1> | <label2> | <label3> |
        | Field    | <value1> | <value2> | <value3> |

    Examples:
      | label1    | value1                       | label2     | value2                   | label3  | value3    | oldContentItemName        | newContentItemName           |
      | value     | Edited Lorem ipsum           |            |                          |         |           | Lorem ipsum               | Edited Lorem ipsum           |
      | name      | Test Name Edited             | email      | edited.email@example.com |         |           | Test Name                 | Test Name Edited             |
      | value     | Edited Lorem ipsum dolor sit |            |                          |         |           | Lorem ipsum dolor sit     | Edited Lorem ipsum dolor sit |
      | value     | Edited Lorem ipsum dolor     |            |                          |         |           | Lorem ipsum dolor         | Edited Lorem ipsum dolor     |
      | text      | Edited Test URL              | url        | http://www.ibexa.co         |         |           | Test URL                  | Edited Test URL              |
