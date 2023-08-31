@systemInformation @IbexaOSS @IbexaHeadless @IbexaExperience @IbexaCommerce
Feature: System info verification
  As an administrator
  In order to customize my website
  I want to have access to all System Information.

  Background:
    Given I am logged as admin
    And I open "System Information" page in admin SiteAccess

  @javascript
  Scenario: Check Product Information
    When I go to "Product" tab in System Information
    Then I see "Product" system information table

  @javascript
  Scenario: Check Composer System Information
    When I go to "Composer" tab in System Information
    Then I see "Composer" system information table
      And I see listed packages
        | Name                     |
        | ibexa/admin-ui           |
        | ibexa/core               |

  @javascript
  Scenario: Check Repository System Information
    When I go to "Repository" tab in System Information
    Then I see "Repository" system information table

  @javascript
  Scenario: Check Hardware System Information
    When I go to "Hardware" tab in System Information
    Then I see "Hardware" system information table

  @javascript
  Scenario: Check PHP System Information
    When I go to "PHP" tab in System Information
    Then I see "PHP" system information table

  @javascript
  Scenario: Check Symfony Kernel System Information
    When I go to "Symfony Kernel" tab in System Information
    Then I see "Symfony Kernel" system information table
      And I see listed bundles
        | Name                     |
        | IbexaAdminUiAssetsBundle |
        | IbexaAdminUiBundle       |
        | IbexaCoreBundle          |

  @javascript
  Scenario: Check services
    When I go to "Services" tab in System Information
    Then I see "Services" system information table
