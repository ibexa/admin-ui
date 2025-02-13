@IbexaOSS @IbexaHeadless @IbexaExperience @IbexaCommerce
Feature: Sections management
  As an administrator
  In order to customize my website
  I want to manage my content Sections.

  Background:
    Given I am logged as admin

  @javascript
  Scenario: Changes can be discarded while creating new Section
    Given I open "Sections" page in admin SiteAccess
    When I create a new Section
      And I set fields
        | label      | value                  |
        | Name       | Test Section           |
        | Identifier | TestSectionIdentifier  |
      And I perform the "Discard" action
    Then I should be on "Sections" page
      And there's no "Test Section" on Sections list

  @javascript
  Scenario: New Section can be added
    Given I open "Sections" page in admin SiteAccess
    When I create a new Section
      And I set fields
        | label      | value                  |
        | Name       | Test Section           |
        | Identifier | TestSectionIdentifier  |
      And I perform the "Save and close" action
    Then I should be on "Test Section" Section page
      And Content items list in is empty for Section
      And Section has proper attributes
        | Name         | Identifier            |
        | Test Section | TestSectionIdentifier |

  @javascript @APIUser:admin
  Scenario: Content item assignation can be discarded
    Given I create "folder" Content items
      | name          | short_name    | parentPath     | language |
      | TestSection   | TestSection   | root           | eng-GB   |
    And I open "Sections" page in admin SiteAccess
    And there's a "Test Section" on Sections list
    When I start assigning to "Test Section" from Sections page
      And I select content "root/TestSection" through UDW
      And I close the UDW window
    Then I should be on "Sections" page
      And the "Test Section" on Sections list has no assigned Content Items

  @javascript @APIUser:admin
  Scenario: Content item can be assigned to section from the Sections list
    Given I create "folder" Content items
      | name          | short_name    | parentPath     | language |
      | TestSection   | TestSection   | Media          | eng-GB   |
    And I open "Sections" page in admin SiteAccess
    And there's a "Test Section" on Sections list
    When I start assigning to "Test Section" from Sections page
      And I select content "Media/TestSection" through UDW
    And I confirm the selection in UDW
    Then success notification that "1 Content items assigned to 'Test Section'" appears
    Then I should be on "Test Section" Section page
      And content items list in section "Test Section" contains items
        | Name        | content type | Path  |
        | TestSection | Folder       | Media |

  @javascript
  Scenario: Changes can be discarded while editing Section
    Given I open "Sections" page in admin SiteAccess
    And there's a "Test Section" on Sections list
    When I start editing "Test Section" from Sections list
      And I set fields
        | label | value               |
        | Name  | Test Section edited |
      And I perform the "Discard changes" action
    Then I should be on "Sections" page
      And there's a "Test Section" on Sections list
      And there's no "Test Section edited" on Sections list

  @javascript
  Scenario: Section can be edited
    Given I open "Sections" page in admin SiteAccess
    And there's a "Test Section" on Sections list
    When I start editing "Test Section" from Sections list
      And I set fields
        | label | value               |
        | Name  | Test Section edited |
      And I perform the "Save and close" action
    Then I should be on "Test Section edited" Section page
      And notification that "Section" "Test Section edited" is updated appears

  @javascript
  Scenario: Changes can be discarded while editing Section from section details
    Given I open "Test Section edited" Section page in admin SiteAccess
    When I start editing the Section
      And I set fields
        | label | value                |
        | Name  | Test Section edited2 |
      And I perform the "Discard changes" action
    Then I should be on "Sections" page
      And there's a "Test Section edited" on Sections list
      And there's no "Test Section edited2" on Sections list

  @javascript
  Scenario: Section can be edited from section details
    Given I open "Test Section edited" Section page in admin SiteAccess
    When I start editing the Section
      And I set fields
        | label | value                |
        | Name  | Test Section edited2 |
      And I perform the "Save and close" action
    Then I should be on "Test Section edited2" Section page
      And notification that "Section" "Test Section edited2" is updated appears

  @javascript
  Scenario: Non-empty section cannot be deleted
    Given I open "Sections" page in admin SiteAccess
    And the "Test Section edited2" on Sections list has assigned Content Items
    Then Section "Test Section edited2" cannot be selected

  @javascript
  Scenario: Content item can be reassigned to section from the Sections details
    Given I open "Sections" page in admin SiteAccess
    And I create a new Section
    And I set fields
      | label      | value                       |
      | Name       | TestSectionAssign           |
      | Identifier | TestSectionAssignIdentifier |
    And I perform the "Save and close" action
    And I open "TestSectionAssign" Section page in admin SiteAccess
    When I start assigning to "TestSectionAssign" Section
    And I select content "Media/TestSection" through UDW
    And I confirm the selection in UDW
    Then success notification that "1 Content items assigned to 'TestSectionAssign'" appears
    And I should be on "TestSectionAssign" Section page
    And content items list in section "TestSectionAssign" contains items
      | Name        | content type | Path  |
      | TestSection | Folder       | Media |
    And I open "Test Section edited2" Section page in admin SiteAccess
    And the "Test Section edited2" has no assigned Content Items

  @javascript
  Scenario: Empty section can be deleted
    Given I open "Sections" page in admin SiteAccess
    And the "Test Section edited2" on Sections list has no assigned Content Items
    When I delete Section "Test Section edited2"
    Then notification that "Section" "Test Section edited2" is removed appears
      And there's no "Test Section edited2" on Sections list

  @javascript
  Scenario: Section can be deleted from section details
    Given I open "Sections" page in admin SiteAccess
    And I create a new Section
    And I set fields
        | label      | value                  |
        | Name       | Test Section           |
        | Identifier | TestSectionIdentifier2 |
      And I perform the "Save and close" action
    And notification that "Section" "Test Section" is created appears
    When I delete the section
    Then notification that "Section" "Test Section" is removed appears
      And there's no "Test Section" on Sections list
