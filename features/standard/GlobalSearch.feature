@IbexaExperience
Feature: Searching for existing item into Back Office.
  As an administrator
  I want to search whichever existing element using global search feature.

  @javascript @APIUser:admin
  Scenario: I am searching for
    Given Example of pages to search
    And ## Place for table of pages to search.
    And I am logged in as admin
    When I click into search textbox
    And Type searching subject
    And Click search button
    Then Searched subject display
