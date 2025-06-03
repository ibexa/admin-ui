@javascript @translation @IbexaOSS @IbexaHeadless @IbexaExperience @IbexaCommerce
Feature: Content item transation

  @APIUser:admin
  Scenario: Publish new translation based on existing translation
    Given I create "folder" Content items in root in "eng-GB"
      | name             | short_name       | short_description | description      |
      | EnglishPublished | EnglishPublished | EnglishPublished  | EnglishPublished |
    And I am logged as admin
    And I'm on Content view Page for "EnglishPublished"
    When I switch to "Translations" tab in Content structure
    And I add new translation "French" basing on "English (United Kingdom)" translation
    And I set content fields
      | label      | value           |
      | Name       | FrenchPublished |
      | Short name | FrenchPublished |
    And I perform the "Publish" action
    Then success notification that "Content published." appears
    And content attributes equal
      | label             | value            |
      | Name              | EnglishPublished |
      | Short name        | EnglishPublished |
      | Short description | EnglishPublished |
      | Description       | EnglishPublished |
    And I choose "French" preview in Content View
    And content attributes equal
      | label             | value            |
      | Name              | FrenchPublished  |
      | Short name        | FrenchPublished  |
      | Short description | EnglishPublished |
      | Description       | EnglishPublished |

  @APIUser:admin
  Scenario: Publish new translation without base translation
    Given I create "folder" Content items in root in "eng-GB"
      | name            | short_name       | short_description | description      |
      | NoBasePublished | NoBasePublished  | NoBasePublished   | NoBasePublished  |
    And I am logged as admin
    And I'm on Content view Page for "NoBasePublished"
    When I switch to "Translations" tab in Content structure
    And I add new translation "French" without base translation
    And I perform the "Publish" action
    Then success notification that "Content published." appears
    And content attributes equal
      | label             | value           |
      | Name              | NoBasePublished |
      | Short name        | NoBasePublished |
      | Short description | NoBasePublished |
      | Description       | NoBasePublished |
    And I choose "French" preview in Content View
    And content attributes equal
      | label             | value               | fieldTypeIdentifier |
      | Name              | Folder              |                     |
      | Short name        | This field is empty | ibexa_string            |
      | Short description | This field is empty | ibexa_richtext          |
      | Description       | This field is empty | ibexa_richtext          |
