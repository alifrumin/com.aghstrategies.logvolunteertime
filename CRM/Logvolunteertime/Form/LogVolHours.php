<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Logvolunteertime_Form_LogVolHours extends CRM_Core_Form {

  public function getVolunteerProjects() {

  }
  public function buildQuickForm() {
    $this->add(
      'text',
      'first_name',
      ts('First Name')
    );
    $this->addRule(
      'first_name',
      'Please enter your first name',
      'required'
    );
    $this->add(
      'text',
      'last_name',
      ts('Last Name')
    );
    $this->addRule(
      'last_name',
      'Please enter your last name',
      'required'
    );
    $this->add(
      'text',
      'email',
      ts('Email')
    );
    $this->addRule(
      'email',
      'Please enter a valid email',
      'email'
    );
    $this->addRule(
      'email',
      'Please enter your email',
      'required'
    );

    $this->add(
      // field type
      'select',
      // field name
      'volunteer_project_select',
      // field label
      'Volunteer Project',
      // list of options
      $this->getProjectOptions(),
      // is required
      TRUE
    );

    $this->add(
      // field type
      'select',
      // field name
      'volunteer_need_select',
      // field label
      'Volunteer Need',
      // list of options
      $this->getVolunteerNeeds(),
      // is required
      TRUE
    );

    $this->add(
      'text',
      'hours_logged',
      ts('Hours Volunteered')
    );

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    // captcha
    // $captcha = CRM_Utils_ReCAPTCHA::singleton();
    // $captcha->add($this);
    // $this->assign("isCaptcha", TRUE);

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();
    $individualParams = array();
    $individualFields = array(
      'first_name',
      'last_name',
      'email',
    );
    foreach ($individualFields as $field) {
      if (!empty($values[$field])) {
        $individualParams[$field] = $values[$field];
      }
    }
    //Dedupe contact
    $dedupeParams = CRM_Dedupe_Finder::formatParams($individualParams, 'Individual');
    $dedupeParams['check_permission'] = FALSE;
    $dupeIDs = CRM_Dedupe_Finder::dupesByParams($dedupeParams, 'Individual', 'Unsupervised');
    if (is_array($dupeIDs) && !empty($dupeIDs)) {
      $individualParams['id'] = array_shift($dupeIDs);
    }
    $individualParams['contact_type'] = 'Individual';
    try {
      $individual = civicrm_api3('Contact', 'create', $individualParams);
    }
    catch (CiviCRM_API3_Exception $e) {
      $error = $e->getMessage();
      CRM_Core_Error::debug_log_message(ts('API Error %1', array(
        'domain' => 'com.aghstrategies.logvolunteertime',
        1 => $error,
      )));
    }

    // creates a volunteer assingment with hours logged
    try {
      $loghours = civicrm_api3('VolunteerAssignment', 'create', array(
        'volunteer_need_id' => $values['volunteer_need_select'],
        'assignee_contact_id' => $individual['id'],
        'time_completed_minutes' => $values['hours_logged'],
      ));
    }
    catch (CiviCRM_API3_Exception $e) {
      $error = $e->getMessage();
      CRM_Core_Error::debug_log_message(ts('API Error %1', array(
        'domain' => 'com.aghstrategies.logvolunteertime',
        1 => $error,
      )));
    }

    parent::postProcess();
  }

  /**
   * Gets Options for Volunteer Projects select
   * @return [type] [description]
   */
  public function getProjectOptions() {
    try {
      $results = civicrm_api3('VolunteerProject', 'get');
    }
    catch (CiviCRM_API3_Exception $e) {
      $error = $e->getMessage();
      CRM_Core_Error::debug_log_message(ts('API Error %1', array(
        'domain' => 'com.aghstrategies.logvolunteertime',
        1 => $error,
      )));
    }
    $projects = $results['values'];
    $options = array('' => ts('- select -'));
    if (!empty($projects)) {
      foreach ($projects as $project) {
        $options[$project['id']] = ts($project['title']);
      }
    }
    return $options;
  }

  //TODO: Need to create select two of volunteer Needs based on Project picked
  /**
   * Gets Options for Volunteer Projects select
   * @return [type] [description]
   */
  public function getVolunteerNeeds() {
    try {
      $result = civicrm_api3('VolunteerNeed', 'get', array(
        //need to use variable from VolunteerProject select instead of 1
        'sequential' => 1,
        'project_id' => 1,
      ));
    }
    catch (CiviCRM_API3_Exception $e) {
      $error = $e->getMessage();
      CRM_Core_Error::debug_log_message(ts('API Error %1', array(
        'domain' => 'com.aghstrategies.logvolunteertime',
        1 => $error,
      )));
    }
    $needs = $result['values'];
    $options = array('' => ts('- select -'));
    if (!empty($needs)) {
      foreach ($needs as $need) {
        $options[$need['id']] = ts($need['role_label']);
      }
    }
    return $options;
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      if ($element->getName() == 'g-recaptcha-response') {
        continue;
      }
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
