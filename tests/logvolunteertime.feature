Feature: Creates a form that allows users to Log Thier Own Volunteer Hours if they have the permission "CiviVolunteer: log own hours".

  Scenario: An anonymous user who is registered to volunteer visits the form to log volunteer hours
    Given a user is not logged in and is registered to volunteer
    When they visit the civicrm/LogVolHours page
    And accurately enter the information corresponding to the volunteer activity on their contact record
    Then On submit that activity is updated to reflect the number in "Hours Volunteered" ard the activity status is changed to completed.

  Scenario: An anonymous user who does not have a contact record and is not registered to volunteer visits the form to log volunteer hours
    Given a user is not logged in and is registered to volunteer and does not have a contact record
    When they visit and complete the civicrm/LogVolHours page
    Then On submit a contact is created with their email and name and an activity with status completed is created with all information they entered


# Gold Feature:

# Scenario: A logged in user visits the form to log volunteer hours for event
#   Given a user is logged in
#   When they visit the form the form is prepopulated with their first and last name and the select2 shows only the events they are registered for
#   Then there is an additional option to register for other events. two
#   When they hit submit their hours are logged (if its for an event they are signed up to volunteer at on that record if not a new record is created)
#
# Scenario: A logged in user visits the form to log volunteer hours for event
#   Given a user is logged in
#   When they visit the form the form is prepopulated with their first and last name

# Scenario: A user goes to log hours from the log hours button on the drupal view of all events
#   Given a user visits the form via the drupal view link
#   Then the select 2 defaults to the event with that id
#   When they enter a first and last name that matches a registered volunteer for that event they can log hours for that contact
#   When the name does not match an new contact is created as well as a record of that person signing up to volunteer and their logged hours
