Automation Dashboard
===

This is a Drupal module. Copy the module into the modules folder and enable it as usual.

This module provides a REST endpoint for POST requests i.e. /dynamicjs

Example post data:
```json
{
  "event": "scenario_failed",
  "scenarios": {
    "Feature Context SRP": {
      "Test search page": {
        "description": "In order to work with automation\nAs a QA dev\nI want to test custom step definitions for srp",
        "steps": [
          "Given I go to the homepage",
          "Given I search product \"a\"",
          "Then the \"h1\" element should contain \"bar results\""
        ],
        "feature_file": "\/tests\/behat\/build\/features\/vs-sa-uat-en\/srp.feature",
        "line": 8,
        "error_message": "The string \"bar results\" was not found in the HTML of the element matching css \"h1\".",
        "screenshots": []
      }
    }
  },
  "brand": "vs",
  "market": "sa"
}
```

You can use [Behat Dashboard Notifier](https://github.com/marcelovani/behat-dashboard-notifier/) extension to do the POST requests.

When the Dashboard receives a post request, it will generate Js files for the brand/market.

The website can be viewed by Authenticated users on the url /dashboard or home page.
