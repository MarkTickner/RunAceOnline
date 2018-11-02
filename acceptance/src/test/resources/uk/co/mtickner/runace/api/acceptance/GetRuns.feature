Feature: Get runs

  Scenario: User should be able to retrieve their runs
    Given I am user 1
    When I call the get runs endpoint
    Then my runs should be returned successfully
