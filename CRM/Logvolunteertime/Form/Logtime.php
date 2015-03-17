<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */

class CRM_Logvolunteertime_Form_Logtime extends CRM_Volunteer_Form_VolunteerSignUp {

  public function preProcess() {
    // VOL-71: permissions check is moved from XML to preProcess function to support
    // permissions-challenged Joomla instances
    if (CRM_Core_Config::singleton()->userPermissionClass->isModulePermissionSupported()
    && !CRM_Volunteer_Permission::check('log own volunteer hours')
    ) {
      CRM_Utils_System::permissionDenied();
    }
    parent::preProcess();
  }

  public function buildQuickForm() {
    parent::buildQuickForm();
    $this->add('text', "scheduled_duration", 'Scheduled Duration');
    $this->add('text', "actual_duration", 'Actual Duration');
    $this->add('text', "other_role", 'Role');
    $this->addDateTime("new_start_date", 'Start Date', FALSE, array('formatType' => 'activityDateTime'));
    CRM_Utils_System::setTitle(ts('Log Your Own Volunteer Hours for %1', array(1 => $this->_project->title)));

  }

  public function postProcess() {
    $cid = CRM_Utils_Array::value('userID', $_SESSION['CiviCRM'], NULL);
    $values = $this->controller->exportValues();
    $isFlexible = FALSE;
    // Role id is not present in form $values when the only public need is the flexible need.
    // So if role id is not set OR if it matches flexible role id constant then use the flexible need id
    if (!isset($values['volunteer_role_id']) || (int) CRM_Utils_Array::value('volunteer_role_id', $values) === CRM_Volunteer_BAO_Need::FLEXIBLE_ROLE_ID) {
      $isFlexible = TRUE;
      foreach ($this->_project->needs as $n) {
        if ($n['is_flexible'] === '1') {
          $values['volunteer_need_id'] = $n['id'];
          break;
        }
      }
    }
    unset($values['volunteer_role_id']); // we don't need this anymore

    $params = array(
      'id' => CRM_Utils_Array::value('volunteer_need_id', $values),
      'version' => 3,
    );
    $need = civicrm_api('VolunteerNeed', 'getsingle', $params);
    $profile_fields = CRM_Core_BAO_UFGroup::getFields($this->_ufgroup_id);
    $profile_values = array_intersect_key($values, $profile_fields);
    $builtin_values = array_diff_key($values, $profile_values);

    // Search for duplicate
    if (!$cid) {
      $dedupeParams = CRM_Dedupe_Finder::formatParams($profile_values, 'Individual');
      $dedupeParams['check_permission'] = FALSE;
      $ids = CRM_Dedupe_Finder::dupesByParams($dedupeParams, 'Individual');
      if ($ids) {
        $cid = $ids[0];
      }
    }

    $cid = CRM_Contact_BAO_Contact::createProfileContact(
    $profile_values,
    $profile_fields,
    $cid,
    NULL,
    $this->_ufgroup_id
    );

    $activity_statuses = CRM_Activity_BAO_Activity::buildOptions('status_id', 'create');

    $builtin_values['activity_date_time'] = CRM_Utils_Array::value('start_time', $need);
    $builtin_values['assignee_contact_id'] = $cid;
    $builtin_values['is_test'] = ($this->_mode === 'test' ? 1 : 0);
    // Below we assume that volunteers are always signing up only themselves;
    // For now this is a safe assumption, but we may need to revisit this.
    $builtin_values['source_contact_id'] = $cid;

    // Set status to Available if user selected Flexible Need,
    // else set to Scheduled.
    $builtin_values['time_scheduled_minutes'] = CRM_Utils_Array::value('scheduled_duration', $value);
    $builtin_values['time_completed_minutes'] = CRM_Utils_Array::value('actual_duration', $value);
    $builtin_values['status_id'] = CRM_Utils_Array::key('Completed', $activity_statuses);
    $builtin_values['subject'] = $this->_project->title;
    $builtin_values['time_scheduled_minutes'] = CRM_Utils_Array::value('duration', $need);
    if (CRM_Utils_Array::value('other_role', $builtin_values) != "") {
      $builtin_values['details'] .= "\r\n Other Role: " . CRM_Utils_Array::value('other_role', $builtin_values);
    }
    CRM_Volunteer_BAO_Assignment::createVolunteerActivity($builtin_values);

    $statusMsg = ts('Your hours have been saved. Thank you!', array('domain' => 'org.civicrm.volunteer'));
    CRM_Core_Session::setStatus($statusMsg, '', 'success');
    CRM_Utils_System::redirect($this->_destination);
  }

}
