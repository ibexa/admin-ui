# Coverage for the inline "quick edit" affordance on the Content view's Fields tab
# (content_view_fields.html.twig + admin.location.quick.field.edit.js).
#
# `inline_field_edit.enabled` defaults to false (see InlineFieldEdit configuration parser), and
# this suite has no in-suite mechanism to flip a siteaccess configuration flag for a scenario or
# a project edition: existing config-gated features (e.g. `user_profile.enabled`, also
# default-false) are instead covered by simply omitting the feature file's scenarios from the
# `@IbexaOSS`/`@IbexaHeadless`/`@IbexaExperience`/`@IbexaCommerce` tags used to select suites in
# CI (see UserProfile.feature, which carries no `@IbexaOSS` tag for exactly this reason), relying
# on the target project's own configuration to turn the feature on where it is meant to run.
#
# `inline_field_edit.enabled` has no project shipping it on by default anywhere yet, so every
# scenario below that needs it on is deliberately left untagged for any edition - they will not
# run under any of the `browser-tests.yaml` CI jobs until a project provides that configuration
# override (e.g. `ibexa.system.<siteaccess-group>.inline_field_edit.enabled: true`), which is not
# something admin-ui's own repository (a bundle, with no `config/packages` of its own) can supply.
# Only the "feature flag is off" scenario needs no such override, since it asserts today's actual
# default, so only that one keeps the standard edition tags.
Feature: Inline quick edit of simple Field values from the Content view
  As an editor
  In order to make small corrections without opening the full content editor
  I want to change a simple Field's value directly from the Content view and publish it

  Background:
    Given I am logged as admin
    And I create a "Quick Edit CT" content type in "Content" with "quick_edit_ct" identifier
      | Field Type    | Name      | Identifier | Required | Searchable | Translatable |
      | Text line     | Name      | name       | yes      | yes        | yes          |
      | Text line     | Headline  | headline   | no       | no         | yes          |
      | Text line     | Subtitle  | subtitle   | no       | no         | yes          |
      | Email address | Email     | email      | no       | no         | yes          |
      | Integer       | Score     | score      | no       | no         | yes          |
      | Checkbox      | Active    | active     | no       | no         | yes          |
      | Date          | Published | published  | no       | no         | yes          |
    And a "quick_edit_ct" Content item named "QuickEditItem" exists in root
      | name          | headline           | subtitle           | email                 | score | active | published  |
      | QuickEditItem | Original headline  | Original subtitle  | original@example.com  | 10    | true   | 2020-06-15 |
    And I'm on Content view Page for "QuickEditItem"

  @javascript
  Scenario: Quick edit updates a text field
    When I double-click the "Headline" field
    And I set the quick-edit input for "Headline" to "Updated headline"
    And I click the quick-edit confirm button for "Headline"
    Then success notification that "Field updated and published." appears
    And content attributes equal
      | label    | value             |
      | Headline | Updated headline  |

  @javascript
  Scenario: Quick edit updates a numeric field
    When I double-click the "Score" field
    And I set the quick-edit input for "Score" to "42"
    And I click the quick-edit confirm button for "Score"
    Then success notification that "Field updated and published." appears
    And content attributes equal
      | label | value |
      | Score | 42    |

  # This is also the coverage for "a cleared checkbox sends false, not null": Active starts
  # checked (see Background), and this scenario unchecks it. A "cleared checkbox" and "the
  # boolean family's happy path" are the same user action, so one scenario stands for both.
  @javascript
  Scenario: Quick edit updates a boolean field, publishing a cleared checkbox as false
    When I double-click the "Active" field
    And I toggle the quick-edit checkbox for "Active"
    And I click the quick-edit confirm button for "Active"
    Then success notification that "Field updated and published." appears
    And content attributes equal
      | label  | value |
      | Active | false |

  # Also stands as this suite's coverage for "ibexa_date round-trips unchanged": the value typed
  # here is republished and re-displayed unchanged. What this scenario cannot prove is that the
  # round trip is unchanged specifically with the browser clock in a non-UTC timezone - see the
  # task report for why that precondition is not reachable through this suite's driver API.
  @javascript
  Scenario: Quick edit updates a date field and republishes it unchanged
    When I double-click the "Published" field
    And I set the quick-edit input for "Published" to "2021-09-01"
    And I click the quick-edit confirm button for "Published"
    Then success notification that "Field updated and published." appears
    And content attributes equal
      | label     | value      |
      | Published | 2021-09-01 |

  @javascript
  Scenario: Cancelling quick edit via the cancel button discards the change
    When I double-click the "Headline" field
    And I set the quick-edit input for "Headline" to "Should be discarded"
    And I click the quick-edit cancel button for "Headline"
    Then there should be 0 open quick-edit editors
    And content attributes equal
      | label    | value              |
      | Headline | Original headline  |

  @javascript
  Scenario: Cancelling quick edit via the Escape key discards the change
    When I double-click the "Headline" field
    And I set the quick-edit input for "Headline" to "Should be discarded"
    And I press the Escape key while quick-editing "Headline"
    Then there should be 0 open quick-edit editors
    And content attributes equal
      | label    | value              |
      | Headline | Original headline  |

  @javascript
  Scenario: Cancelling quick edit via an outside click discards the change
    When I double-click the "Headline" field
    And I set the quick-edit input for "Headline" to "Should be discarded"
    And I click outside the quick-edit editor
    Then there should be 0 open quick-edit editors
    And content attributes equal
      | label    | value              |
      | Headline | Original headline  |

  @javascript @APIUser:admin
  Scenario: Dismissing the quick-edit draft conflict keeps editing without publishing
    Given I create a new Draft for "QuickEditItem" Content item in "eng-GB"
      | subtitle                 |
      | Draft made by another editor |
    And I'm on Content view Page for "QuickEditItem"
    When I double-click the "Headline" field
    And I set the quick-edit input for "Headline" to "Should not be published"
    And I click the quick-edit confirm button for "Headline"
    Then the quick-edit draft conflict should appear
    When I dismiss the quick-edit draft conflict
    Then the "Headline" field should still be open for quick edit
    And there should be 1 draft versions for the content item

  @javascript @APIUser:admin
  Scenario: Confirming the quick-edit draft conflict publishes a new version and leaves the existing draft untouched
    Given I create a new Draft for "QuickEditItem" Content item in "eng-GB"
      | subtitle                 |
      | Draft made by another editor |
    And I'm on Content view Page for "QuickEditItem"
    When I double-click the "Headline" field
    And I set the quick-edit input for "Headline" to "Published via quick edit"
    And I click the quick-edit confirm button for "Headline"
    Then the quick-edit draft conflict should appear
    When I confirm the quick-edit draft conflict
    Then success notification that "Field updated and published." appears
    And content attributes equal
      | label    | value                     |
      | Headline | Published via quick edit |
    And there should be 1 draft versions for the content item

  # "Server's message, not a JSON parse error" is exercised at the PATCH step rather than PUBLISH:
  # ibexa_email has no client-side validate() (see quick.field.edit.editors.js), so an invalid
  # address reaches the server unfiltered, while PATCH and PUBLISH share the exact same
  # assertResponseOk()/getRestErrorMessage() error path and the same cleanup, so this is a
  # faithful stand-in for a failed-publish scenario without needing a less certain, harder to
  # verify fixture (e.g. a permission split between edit and publish). See the task report.
  @javascript
  Scenario: A failed save surfaces the server's own message and leaves no draft behind
    When I double-click the "Email" field
    And I set the quick-edit input for "Email" to "not-an-email"
    And I click the quick-edit confirm button for "Email"
    Then error notification that "value must be a valid email address" appears
    And there should be 0 draft versions for the content item

  # Two rapid, non-overlapped double-clicks already yield exactly one editor under normal
  # open-close-open sequencing; this scenario cannot force genuine network-level overlap between
  # the two prefills (no delay-injection is exposed by this suite's driver API), but the
  # postcondition it checks - exactly one editor, and the field last opened is the one patched -
  # is exactly the one the brief asks for.
  @javascript
  Scenario: Two rapid opens on different fields leave exactly one editor open
    When I double-click the "Headline" field
    And I double-click the "Subtitle" field
    Then there should be 1 open quick-edit editors
    When I set the quick-edit input for "Subtitle" to "Typed into subtitle"
    And I click the quick-edit confirm button for "Subtitle"
    Then success notification that "Field updated and published." appears
    And content attributes equal
      | label    | value                |
      | Subtitle | Typed into subtitle  |
      | Headline | Original headline    |

  # Deliberately does not use the draft-conflict modal's own await window: a real user cannot
  # click a field row hidden behind that modal's backdrop either, so an interaction "refused"
  # only because Bootstrap's backdrop intercepts it would not be exercising this guard at all.
  # This instead targets the real window the guard exists for - between clicking confirm and the
  # eventual page reload - which is timing-dependent since this suite exposes no way to hold a
  # REST call open on demand; see the task report.
  @javascript
  Scenario: An open attempted while a save is in flight is refused
    When I double-click the "Headline" field
    And I set the quick-edit input for "Headline" to "In flight"
    And I click the quick-edit confirm button for "Headline"
    Then there should be 1 open quick-edit editors
    When I double-click the "Subtitle" field
    Then there should be 1 open quick-edit editors

  @javascript @APIUser:admin
  Scenario: Field offers no quick-edit affordance without edit permission
    Given I create a user group "QuickEditReadOnlyGroup"
    And I create a role "quickEditReadOnlyRole" with policies
      | module  | function |
      | content | read     |
    And I create a user "QuickEditReadOnlyUser" with last name "QuickEditReadOnly" in group "QuickEditReadOnlyGroup"
    And I assign user "QuickEditReadOnlyUser" to role "quickEditReadOnlyRole"
    And I open Login page in admin SiteAccess
    And I log in as "QuickEditReadOnlyUser" with password "Passw0rd-42"
    And I'm on Content view Page for "QuickEditItem"
    Then the "Headline" field should not offer quick edit

  # The only scenario in this file that needs no configuration override: it asserts today's
  # actual default (inline_field_edit.enabled: false), so it keeps the normal edition tags.
  @javascript @IbexaOSS @IbexaHeadless @IbexaExperience @IbexaCommerce
  Scenario: No quick-edit affordance appears while the feature flag is off
    Then the "Headline" field should not offer quick edit
