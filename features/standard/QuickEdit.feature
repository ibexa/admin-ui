# Coverage for the inline "quick edit" affordance on the Content view's Fields tab
# (content_view_fields.html.twig + admin.location.quick.field.edit.js).
#
# ============================================================================================
# PREREQUISITE - READ BEFORE RUNNING: every scenario below except the last one CANNOT PASS
# against this suite's current configuration, and will hard-fail at the first double-click.
#
#   `inline_field_edit.enabled` (src/bundle/DependencyInjection/Configuration/Parser/
#   InlineFieldEdit.php) defaults to FALSE. Without it set to true for the siteaccess/siteaccess
#   group the "admin" login runs under, content_view_fields.html.twig never renders the
#   `data-quick-edit` attribute, and every "I double-click the ... field" step in this file will
#   time out finding an editor that the page never offers.
#
#   To run anything in this file besides the last scenario, the target project's own
#   configuration must set, for the relevant siteaccess/siteaccess group:
#       ibexa.system.<siteaccess-group>.inline_field_edit.enabled: true
#   This is not something admin-ui's own repository can supply - it is a bundle, with no
#   project-level `config/packages` of its own - so the override has to live in whichever full
#   Ibexa project `browser-tests.yml` provisions to run this suite. No such override exists
#   anywhere today.
# ============================================================================================
#
# Because of that, this suite also has no in-suite mechanism to flip a siteaccess configuration
# flag for a scenario or a project edition: existing config-gated features (e.g.
# `user_profile.enabled`, also default-false) are instead covered by simply omitting the feature
# file's scenarios from the `@IbexaOSS`/`@IbexaHeadless`/`@IbexaExperience`/`@IbexaCommerce` tags
# used to select suites in CI (see UserProfile.feature, which carries no `@IbexaOSS` tag for
# exactly this reason), relying on the target project's own configuration to turn the feature on
# where it is meant to run. `inline_field_edit.enabled` has no project shipping it on by default
# anywhere yet, so the same treatment applies here: every scenario below that needs it on is left
# without an edition tag - they will not run under any of the `browser-tests.yaml` CI jobs, each
# of which filters by exactly one edition tag.
#
# On top of that, and because omitting a tag reads as "not yet categorised" rather than "cannot
# pass without a precondition", every one of those scenarios additionally carries
# `@requires-config:inline_field_edit.enabled` - a new tag, since no existing tag in this suite
# names a configuration prerequisite; it follows the closest existing convention for a
# colon-qualified tag (`@APIUser:admin`) rather than inventing an unrelated style. Only the
# "feature flag is off" scenario needs no override and no such tag, since it asserts today's
# actual default; it keeps the standard edition tags instead.
#
# Per the approved design, the editor row renders no confirm/cancel buttons at all for most field
# types - Enter saves, Escape/an outside click discards - so most scenarios below drive save via
# "I press the Enter key while quick-editing ...". The one exception is `ibexa_text` (the
# "Description" field added to the content type below): its textarea treats a plain Enter as a
# newline, so it alone renders an explicit Save/Discard button pair, and "Quick edit updates a
# text field via the Save button"/"Cancelling quick edit via the Discard button discards the
# change" below are the coverage for that pair specifically.
Feature: Inline quick edit of simple Field values from the Content view
  As an editor
  In order to make small corrections without opening the full content editor
  I want to change a simple Field's value directly from the Content view and publish it

  Background:
    Given I am logged as admin
    And I create a "Quick Edit CT" content type in "Content" with "quick_edit_ct" identifier
      | Field Type    | Name        | Identifier  | Required | Searchable | Translatable |
      | Text line     | Name        | name        | yes      | yes        | yes          |
      | Text line     | Headline    | headline    | no       | no         | yes          |
      | Text line     | Subtitle    | subtitle    | no       | no         | yes          |
      | Email address | Email       | email       | no       | no         | yes          |
      | Integer       | Score       | score       | no       | no         | yes          |
      | Checkbox      | Active      | active      | no       | no         | yes          |
      | Date          | Published   | published   | no       | no         | yes          |
      | Text block    | Description | description | no       | no         | yes          |
    And a "quick_edit_ct" Content item named "QuickEditItem" exists in root
      | name          | headline           | subtitle           | email                 | score | active | published  | description           |
      | QuickEditItem | Original headline  | Original subtitle  | original@example.com  | 10    | true   | 2020-06-15 | Original description  |
    And I'm on Content view Page for "QuickEditItem"

  @javascript @requires-config:inline_field_edit.enabled
  Scenario: Quick edit updates a text field via the Save button
    When I double-click the "Description" field
    And I set the quick-edit input for "Description" to "Updated description"
    And I click the quick-edit confirm button for "Description"
    Then success notification that "Field updated and published." appears
    And content attributes equal
      | label       | value                |
      | Description | Updated description  |

  @javascript @requires-config:inline_field_edit.enabled
  Scenario: Quick edit updates a text-line field via Enter
    When I double-click the "Headline" field
    And I set the quick-edit input for "Headline" to "Updated headline"
    And I press the Enter key while quick-editing "Headline"
    Then success notification that "Field updated and published." appears
    And content attributes equal
      | label    | value             |
      | Headline | Updated headline  |

  @javascript @requires-config:inline_field_edit.enabled
  Scenario: Quick edit updates a numeric field via Enter
    When I double-click the "Score" field
    And I set the quick-edit input for "Score" to "42"
    And I press the Enter key while quick-editing "Score"
    Then success notification that "Field updated and published." appears
    And content attributes equal
      | label | value |
      | Score | 42    |

  # This is also the coverage for "a cleared checkbox sends false, not null": Active starts
  # checked (see Background), and this scenario unchecks it. A "cleared checkbox" and "the
  # boolean family's happy path" are the same user action, so one scenario stands for both.
  @javascript @requires-config:inline_field_edit.enabled
  Scenario: Quick edit updates a boolean field, publishing a cleared checkbox as false
    When I double-click the "Active" field
    And I toggle the quick-edit checkbox for "Active"
    And I press the Enter key while quick-editing "Active"
    Then success notification that "Field updated and published." appears
    And content attributes equal
      | label  | value |
      | Active | false |

  # This is a plain round trip only: it proves a date typed here is republished and re-displayed
  # unchanged under whatever timezone this suite's own browser/CI runner happens to have - which
  # in practice is UTC, same as the application server, virtually always. It does NOT prove the
  # ibexa_date UTC-in-both-directions contract holds with the browser clock in a non-UTC
  # timezone: a genuine timezone-offset bug in quick.field.edit.editors.js's UTC arithmetic would
  # stay completely invisible to this scenario, since both sides of the comparison would shift by
  # the same non-existent offset. See the task report: that specific claim has no executable
  # coverage anywhere in this repository and rests solely on code review.
  @javascript @requires-config:inline_field_edit.enabled
  Scenario: Quick edit updates a date field and republishes it unchanged (plain round trip, UTC only)
    When I double-click the "Published" field
    And I set the quick-edit input for "Published" to "2021-09-01"
    And I press the Enter key while quick-editing "Published"
    Then success notification that "Field updated and published." appears
    And content attributes equal
      | label     | value      |
      | Published | 2021-09-01 |

  @javascript @requires-config:inline_field_edit.enabled
  Scenario: Cancelling quick edit via the Discard button discards the change
    When I double-click the "Description" field
    And I set the quick-edit input for "Description" to "Should be discarded"
    And I click the quick-edit cancel button for "Description"
    Then there should be 0 open quick-edit editors
    And content attributes equal
      | label       | value                 |
      | Description | Original description |

  @javascript @requires-config:inline_field_edit.enabled
  Scenario: Cancelling quick edit via the Escape key discards the change
    When I double-click the "Headline" field
    And I set the quick-edit input for "Headline" to "Should be discarded"
    And I press the Escape key while quick-editing "Headline"
    Then there should be 0 open quick-edit editors
    And content attributes equal
      | label    | value              |
      | Headline | Original headline  |

  @javascript @requires-config:inline_field_edit.enabled
  Scenario: Cancelling quick edit via an outside click discards the change
    When I double-click the "Headline" field
    And I set the quick-edit input for "Headline" to "Should be discarded"
    And I click outside the quick-edit editor
    Then there should be 0 open quick-edit editors
    And content attributes equal
      | label    | value              |
      | Headline | Original headline  |

  @javascript @requires-config:inline_field_edit.enabled @APIUser:admin
  Scenario: Dismissing the quick-edit draft conflict keeps editing without publishing
    Given I create a new Draft for "QuickEditItem" Content item in "eng-GB"
      | subtitle                 |
      | Draft made by another editor |
    And I'm on Content view Page for "QuickEditItem"
    When I double-click the "Headline" field
    And I set the quick-edit input for "Headline" to "Should not be published"
    And I press the Enter key while quick-editing "Headline"
    Then the quick-edit draft conflict should appear
    When I dismiss the quick-edit draft conflict
    Then the "Headline" field should still be open for quick edit
    And there should be 1 draft versions for the content item

  @javascript @requires-config:inline_field_edit.enabled @APIUser:admin
  Scenario: Confirming the quick-edit draft conflict publishes a new version and leaves the existing draft untouched
    Given I create a new Draft for "QuickEditItem" Content item in "eng-GB"
      | subtitle                 |
      | Draft made by another editor |
    And I'm on Content view Page for "QuickEditItem"
    When I double-click the "Headline" field
    And I set the quick-edit input for "Headline" to "Published via quick edit"
    And I press the Enter key while quick-editing "Headline"
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
  @javascript @requires-config:inline_field_edit.enabled
  Scenario: A failed save surfaces the server's own message and leaves no draft behind
    When I double-click the "Email" field
    And I set the quick-edit input for "Email" to "not-an-email"
    And I press the Enter key while quick-editing "Email"
    Then error notification that "value must be a valid email address" appears
    And there should be 0 draft versions for the content item

  # Two rapid, non-overlapped double-clicks already yield exactly one editor under normal
  # open-close-open sequencing; this scenario cannot force genuine network-level overlap between
  # the two prefills (no delay-injection is exposed by this suite's driver API), but the
  # postcondition it checks - exactly one editor, and the field last opened is the one patched -
  # is exactly the one the brief asks for.
  @javascript @requires-config:inline_field_edit.enabled
  Scenario: Two rapid opens on different fields leave exactly one editor open
    When I double-click the "Headline" field
    And I double-click the "Subtitle" field
    Then there should be 1 open quick-edit editors
    When I set the quick-edit input for "Subtitle" to "Typed into subtitle"
    And I press the Enter key while quick-editing "Subtitle"
    Then success notification that "Field updated and published." appears
    And content attributes equal
      | label    | value                |
      | Subtitle | Typed into subtitle  |
      | Headline | Original headline    |

  # Deliberately does not use the draft-conflict modal's own await window: a real user cannot
  # click a field row hidden behind that modal's backdrop either, so an interaction "refused"
  # only because Bootstrap's backdrop intercepts it would not be exercising this guard at all.
  # This instead targets the real window the guard exists for - between pressing Enter to save and
  # the eventual page reload - which is timing-dependent since this suite exposes no way to hold a
  # REST call open on demand; see the task report.
  @javascript @requires-config:inline_field_edit.enabled
  Scenario: An open attempted while a save is in flight is refused
    When I double-click the "Headline" field
    And I set the quick-edit input for "Headline" to "In flight"
    And I press the Enter key while quick-editing "Headline"
    Then there should be 1 open quick-edit editors
    When I double-click the "Subtitle" field
    Then there should be 1 open quick-edit editors

  @javascript @requires-config:inline_field_edit.enabled @APIUser:admin
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
  # actual default (inline_field_edit.enabled: false), so it keeps the normal edition tags
  # instead of @requires-config:inline_field_edit.enabled.
  @javascript @IbexaOSS @IbexaHeadless @IbexaExperience @IbexaCommerce
  Scenario: No quick-edit affordance appears while the feature flag is off
    Then the "Headline" field should not offer quick edit
