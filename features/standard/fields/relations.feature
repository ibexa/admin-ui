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
      And a "folder" Content item named "RelationFieldsContainer" exists in root
      | name                    | short_name              |
      | RelationFieldsContainer | RelationFieldsContainer |
      And I am logged as admin
      And I'm on Content view Page for RelationFieldsContainer
    When I start creating a new content "<fieldName> CT"
      And I set content fields
        | label    | <label1>    | <label2> | <label3> |
        | Field    | <value1>    | <value2> | <value3> |
        | Name     | <fieldName> |          |          |
      And I perform the "Publish" action
    Then success notification that "Content published." appears
      And I should be on Content view Page for "RelationFieldsContainer/<contentItemName>"
      And content attributes equal
          | label    | <label1> | <label2> | <label3> |
          | Field    | <value1> | <value2> | <value3> |

    Examples:
      | fieldInternalName    | fieldName                    | fieldSettings                                                         |  label1   | value1                                                                    | label2     | value2                | label3  | value3      | contentItemName           |
      | ibexa_object_relation     | Content relation (single)    |                                                                       | value     | Media/Images                                                              |            |                       |         |             | Images                    |
      | ibexa_object_relation_list | Content relations (multiple) |                                                                       | firstItem | Media/Images                                                              | secondItem | Media/Files           |         |             | Images Files              |

  @javascript @APIUser:admin
  Scenario Outline: Edit content item with given field
    Given I am logged as admin
      And I'm on Content view Page for "RelationFieldsContainer/<oldContentItemName>"
    When I perform the "Edit" action
      And I set content fields
        | label    | <label1> | <label2> | <label3> |
        | Field    | <value1> | <value2> | <value3> |
      And I perform the "Publish" action
    Then success notification that "Content published." appears
      And I should be on Content view Page for "RelationFieldsContainer/<newContentItemName>"
      And content attributes equal
        | label    | <label1> | <label2> | <label3> |
        | Field    | <value1> | <value2> | <value3> |

    Examples:
      | label1    | value1                       | label2     | value2                   | label3  | value3    | oldContentItemName        | newContentItemName           |
      | value     | Media/Files                  |            |                          |         |           | Images                    | Files                        |
      | firstItem | Users/Editors                | secondItem | Media/Multimedia         |         |           | Images Files              | Editors Multimedia           |
