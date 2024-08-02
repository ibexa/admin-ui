@IbexaHeadless @IbexaExperience @IbexaCommerce
Feature: User profile management

  @javascript
  Scenario: Create a new editor
    Given I am logged as admin
    And I'm on Content view Page for "Users/Editors"
    When I start creating a new User using "Editor" content type
    And I set content fields for user
      | label       | value           |
      | First name  | EditorFirstName |
      | Last name   | EditorLastName  |
      | Image       | image1.png      |
    And I set content fields for user
      | label         | Username    | Password    | Confirm password  | Email          | Enabled  |
      | User account  | testeditor  | Test1234pw  | Test1234pw        | test@test.com  | Yes      |
    And I perform the "Create" action
    Then I should be on Content view Page for "/Users/Editors/EditorFirstName EditorLastName"
    And content attributes equal
      | label       | value           |
      | First name  | EditorFirstName |
      | Last name   | EditorLastName  |
      | Image       | image1.png      |
    And content attributes equal
      | label         | Username   | Email          | Enabled  |
      | User account  | testeditor | test@test.com  | Yes      |

  @javascript
  Scenario: User profile is accessible can be edited
    Given I open Login page in admin SiteAccess
    And I log in as "testeditor" with password "Test1234pw"
    And I should be on "Dashboard" page
    And I go to user profile
    And I should be on "User profile" page
    When I edit user profile summary
    And I set content fields for user
      | label       | value             |
      | First name  | EditorFirstName2  |
      | Last name   | EditorLastName2   |
    And I switch to "About" field group
    And I set content fields for user
      | label      | value          |
      | Job Title  | TestJobTitle   |
      | Department | TestDepartment |
      | Location   | TestLocation   |
    And I perform the "Update" action
    Then I should be on "User profile" page
    And I should see a user profile summary with values
      | Full name                        | Email         | Job Title    | Department     | Location     |
      | EditorFirstName2 EditorLastName2 | test@test.com | TestJobTitle | TestDepartment | TestLocation |
