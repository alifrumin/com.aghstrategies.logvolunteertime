Feature: Creates a form that allows users to Log Thier Own Volunteer Hours if they have the permission "CiviVolunteer: log own hours".

  Scenario: An anonymous user who is registered to volunteer visits the form to log volunteer hours
    Given a user is not logged in and is registered to volunteer
    When they visit the civicrm/LogVolHours page
    And accurately enter the information corresponding to the volunteer activity on their contact record
    Then On submit that activity is updated to reflect the number in "Hours Volunteered" and the activity status is changed to completed.

  Scenario: An anonymous user who does not have a contact record and is not registered to volunteer visits the form to log volunteer hours
    Given a user is not logged in and is not registered to volunteer (no exsisting "Volunteer Assignment" activity) and does not have a contact record
    When they visit and complete the civicrm/LogVolHours page
    Then On submit a contact is created with their email and name and an activity with status completed is created with all information they entered

  Scenario: A user goes to log hours from the log hours button on the drupal view of all events (or a url that looks like this: /civicrm/LogVolHours?vid=[volunteer project id number] )
    Given a user visits the form via the drupal view link
    Then the volunteer project select defaults to the volunteer project with the id in the url
    And the Volunteer Needs Select is populated with the needs associated with that project.

  Scenario: A logged in user visits the form to log volunteer hours for event
    Given a user is logged in
    When they visit the form the form is prepopulated with their first and last name and email
    And if that information is edited it is edited on their contact record as well
