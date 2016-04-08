<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Logvolunteertime_Form_LogVolHours extends CRM_Core_Form {
  public function buildQuickForm() {

    // add form elements
    $this->add(
      // field type
      'select',
      // field name
      'favorite_color',
      // field label
      'Favorite Color',
      // list of options
      $this->getColorOptions(),
        // is required
      TRUE
    );

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
    //select2 for volunteer projects
    $this->addEntityRef('field_5', ts('Volunteer Projects'), array(
      'entity' => 'option_value',
      'api' => array(
        'params' => array('option_group_id' => 'volunteer_projects'),
      ),
      'select' => array('minimumInputLength' => 0),
    ));
    $this->add(
      'text',
      'hours_logged',
      ts('Hours Volunteered')
    );

    //captcha
    // $captcha = CRM_Utils_ReCAPTCHA::singleton();
    // $captcha->add($this);
    // $this->assign("isCaptcha", TRUE);
    //
    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();
    $options = $this->getColorOptions();
    CRM_Core_Session::setStatus(ts('You picked color "%1"', array(
      1 => $options[$values['favorite_color']]
    )));
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
    //look for volunteer signups

    parent::postProcess();
  }

  public function getColorOptions() {
    $options = array(
      '' => ts('- select -'),
      '#f00' => ts('Red'),
      '#0f0' => ts('Green'),
      '#00f' => ts('Blue'),
      '#f0f' => ts('Purple'),
    );
    foreach (array('1','2','3','4','5','6','7','8','9','a','b','c','d','e') as $f) {
      $options["#{$f}{$f}{$f}"] = ts('Grey (%1)', array(1 => $f));
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
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
