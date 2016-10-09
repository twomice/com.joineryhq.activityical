<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Activityical_Form_Details extends CRM_Core_Form {
  var $feed;
  var $contact_id;

  public function preProcess() {
    $this->contact_id = CRM_Utils_Array::value('contact_id', $_GET, CRM_Core_Session::singleton()->getLoggedInContactID());
    if (!$this->_flagSubmitted) {
      if (!_activityical_contact_has_feed_group($this->contact_id)) {
        CRM_Core_Error::statusBounce(ts('The given contact does not have an activities iCalendar feed.'));
      }
    }
    $this->feed = CRM_Activityical_Feed::getInstance($this->contact_id);
  }

  public function buildQuickForm() {
    $this->assign('feed_url', $this->feed->getUrl());

    // Show the contact's display name if it's not the current user's contact.
    if ($this->contact_id && ($this->contact_id != CRM_Core_Session::singleton()->getLoggedInContactID())) {
      $not_found_error = ts('Could not find the given contact.');
      $api_params = array(
        'sequential' => 1,
        'id' => $this->contact_id,
      );
      try {
        $result = civicrm_api3('contact', 'get', $api_params);
      }
      catch (CiviCRM_API3_Exception $e) {
          CRM_Core_Error::statusBounce($not_found_error);
      }
      if (empty($result['id'])) {
        CRM_Core_Error::statusBounce($not_found_error);
      }
      $this->assign('is_other_contact', TRUE);
      $display_name = ($result['values'][0]['display_name'] ?: ts('[contact ID %1]', array(1 => $this->contact_id)));
      $this->assign('display_name', $display_name);
    }

    $this->addElement('hidden', 'contact_id');

    // add form buttons
    if (!empty($display_name)) {
      $button_name = ts('Rebuild feed URL now, for %1', array(1 => $display_name));
    }
    else {
      $button_name = ts('Rebuild feed URL now');
    }
    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => $button_name,
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());

    // Add form resources.
    CRM_Core_Resources::singleton()->addStyleFile('com.joineryhq.activityical', 'css/activityical.css');
    CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.activityical', 'js/activityical_feed_details.js');

    parent::buildQuickForm();
  }

  public function postProcess() {
    // This form really only does one thing if submitted, which is to rebuild
    // the feed URL.
    $this->feed = CRM_Activityical_Feed::getInstance($this->_submitValues['contact_id']);
    $this->feed->generateHash();
    CRM_Core_Session::setStatus(" ", ts('URL rebuilt'), "success");
    $extra = (!empty($this->_submitValues['contact_id']) ? "&contact_id={$this->_submitValues['contact_id']}" : '');
    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/activityical/details', 'reset=1' . $extra));
  }

  public function setDefaultValues() {
    return array(
      'contact_id' => $this->contact_id,
    );
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
