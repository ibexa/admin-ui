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
      And a "folder" Content item named "OtherFieldsContainer" exists in root
      | name                 | short_name           |
      | OtherFieldsContainer | OtherFieldsContainer |
      And I am logged as admin
      And I'm on Content view Page for OtherFieldsContainer
    When I start creating a new content "<fieldName> CT"
      And I set content fields
        | label    | <label1>    |
        | Field    | <value1>    |
        | Name     | <fieldName> |
      And I perform the "Publish" action
    Then success notification that "Content published." appears
      And I should be on Content view Page for "OtherFieldsContainer/<contentItemName>"
      And content attributes equal
          | label    | <label1> | fieldTypeIdentifier   |
          | Field    | <value1> | <fieldTypeIdentifier> |

    Examples:
      | fieldInternalName    | fieldName                    | fieldSettings                                                         |  label1   | value1                                                                    | contentItemName           | fieldTypeIdentifier | 
      | ibexa_selection          | Selection                    | is_multiple:false,options:A first-Bielefeld-TestValue-Turtles-Zombies | value     | TestValue                                                                 | TestValue                 | ibexa_selection         |
      | ibexa_boolean            | Checkbox                     |                                                                       | value     | true                                                                      | 1                         |                     |
      | ibexa_email              | Email address                |                                                                       | value     | email@example.com                                                         | email@example.com         |                     |
      | ibexa_float              | Float                        |                                                                       | value     | 11.11                                                                     | 11.11                     |                     |
      | ibexa_isbn               | ISBN                         |                                                                       | value     | 978-3-16-148410-0                                                         | 978-3-16-148410-0         |                     |
      | ibexa_integer            | Integer                      |                                                                       | value     | 1111                                                                      | 1111                      |                     |
      | ibexa_keyword            | Keywords                     |                                                                       | value     | keyword1                                                                  | keyword1                  |                     |
      | ibexa_matrix             | Matrix                       | Min_rows:2,Columns:col1-col2-col3                                     | value     | col1:col2:col3,Ala:miała:kota,Szpak:dziobał:bociana,Bociana:dziobał:szpak | Matrix                    |                     |

  @javascript @APIUser:admin
  Scenario Outline: Edit content item with given field
    Given I am logged as admin
      And I'm on Content view Page for "OtherFieldsContainer/<oldContentItemName>"
    When I perform the "Edit" action
      And I set content fields
        | label    | <label1> |
        | Field    | <value1> |
      And I perform the "Publish" action
    Then success notification that "Content published." appears
      And I should be on Content view Page for "OtherFieldsContainer/<newContentItemName>"
      And content attributes equal
        | label    | <label1> | fieldTypeIdentifier   |
        | Field    | <value1> | <fieldTypeIdentifier> |

    Examples:
      | label1    | value1                                    | oldContentItemName        | newContentItemName           | fieldTypeIdentifier |
      | value     | Bielefeld                                 | TestValue                 | Bielefeld                    | ibexa_selection         |
      | value     | false                                     | 1                         | 0                            |                     |
      | value     | edited.email@example.com                  | email@example.com         | edited.email@example.com     |                     |
      | value     | 12.34                                     | 11.11                     | 12.34                        |                     |
      | value     | 0-13-048257-9                             | 978-3-16-148410-0         | 0-13-048257-9                |                     |
      | value     | 1234                                      | 1111                      | 1234                         |                     |
      | value     | keyword2                                  | keyword1                  | keyword2                     |                     |
      | value     | col1:col2:col3,11:12:13,21:22:23,31:32:33 | Matrix                    | Matrix                       |                     |
