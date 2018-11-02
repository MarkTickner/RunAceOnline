Feature: Get runs

  Scenario: User should be able to retrieve their runs
    When I get my runs
    Then my runs should be returned successfully
