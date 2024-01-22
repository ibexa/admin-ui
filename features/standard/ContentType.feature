@IbexaOSS @IbexaHeadless @IbexaExperience @IbexaCommerce
Feature: Content types management
  As an administrator
  In order to customize my Ibexa installation
  I want to manage my content types.

  Background:
    Given I am logged as admin

  @javascript
  Scenario: Changes can be discarded while creating content type
    Given I'm on content type Page for "Content" group
    When I create a new content type
      And I set fields
      | label      | value                     |
      | Name       | Test content type         |
      | Identifier | TestContentTypeIdentifier |
      And I perform the "Discard" action
    Then I should be on content type group page for "Content" group
      And there's no "Test content type" on content types list

  @javascript
  Scenario: New content type can be added to content type group
    Given I'm on content type Page for "Content" group
    When I create a new content type
      And I set fields
        | label                | value                     |
        | Name                 | Test content type         |
        | Identifier           | TestContentTypeIdentifier |
        | Content name pattern | <name>                    |
      And I select "Content" category to content type definition
      And I add field "Country" to content type definition
      And I set "Name" to "Country field" for "Country" field
      And I perform the "Save and close" action
    Then notification that "content type" "New content type" is updated appears
    Then I should be on content type page for "Test content type"
      And content type has proper Global properties
        | label                | value                     |
        | Name                 | Test content type         |
        | Identifier           | TestContentTypeIdentifier |
        | Content name pattern | <name>                    |
      And content type "Test content type" has proper fields
        | fieldName       | fieldType |
        | Country field   | ezcountry |

  @javascript @APIUser:admin
  Scenario: Changes can be discarded while editing content type
    Given I create a "TestDiscard CT" content type in "Content" with "testdiscard" identifier
      | Field Type  | Name        | Identifier          | Required | Searchable | Translatable | Settings       |
      | Text line   | Name        | name	            | no      | yes	      | yes          |                  |
    And I'm on content type Page for "Content" group
    And there's a "TestDiscard CT" on content types list
    When I start editing content type "TestDiscard CT"
      And I set fields
        | label | value                    |
        | Name  | Test content type edited |
      And I perform the "Discard" action
    Then I should be on content type group page for "Content" group
      And there's a "TestDiscard CT" on content types list
      And there's no "Test content type edited" on content types list

  @javascript @APIUser:admin
  Scenario: New Field can be added while editing content type
    Given I create a "TestEdit CT" content type in "Content" with "testedit" identifier
      | Field Type  | Name        | Identifier          | Required | Searchable | Translatable | Settings       |
      | Text line   | Name        | name	            | no      | yes	      | yes          |                  |
    And I'm on content type Page for "Content" group
    When I start editing content type "TestEdit CT"
      And I set fields
        | label | value                    |
        | Name  | Test content type edited |
      And I add field "Date" to content type definition
    And I set "Name" to "DateField" for "Date" field
      And I perform the "Save and close" action
    Then success notification that "content type 'TestEdit CT' updated." appears
    Then I should be on content type page for "Test content type edited"
      And content type has proper Global properties
        | label                | value                     |
        | Name                 | Test content type edited  |
        | Identifier           | testedit                  |
        | Content name pattern | <name>                    |
      And content type "Test content type" has proper fields
        | fieldName      | fieldType |
        | Name           | ezstring  |
        | DateField      | ezdate    |

  @javascript @APIUser:admin
  Scenario: Content type can be deleted from content type group
    Given I create a "TestDelete CT" content type in "Content" with "testdelete" identifier
      | Field Type  | Name        | Identifier          | Required | Searchable | Translatable | Settings       |
      | Text line   | Name        | name	            | no      | yes	      | yes          |                  |
    And I'm on content type Page for "Content" group
    And there's a "TestDelete CT" on content types list
    When I delete "TestDelete CT" content type
    Then success notification that "content type 'TestDelete CT' deleted." appears
    And there's no "TestDelete CT" on content types list
