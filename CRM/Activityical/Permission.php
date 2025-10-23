<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class CRM_Activityical_Permission {

  /**
   * We only need one instance of this object. So we use the singleton
   * pattern and cache the instance in this variable
   *
   * @var CRM_Activityical_Permission
   */
  private static $_singleton = NULL;

  /**
   * Parameters for permissioning, defaults to $_GET
   * @var params
   */
  private $_params;

  /**
   * The constructor. Use self::singleton() to create an instance.
   */
  private function __construct() {
  }

  /**
   * Singleton function used to manage this object.
   *
   * @return CRM_Activityical_Permission
   */
  public static function &singleton($params = NULL) {
    if (self::$_singleton === NULL) {
      if ($params === NULL) {
        $params = $_GET;
      }
      self::$_singleton = new CRM_Activityical_Permission();
      self::$_singleton->_params = $params;
    }
    return self::$_singleton;
  }

  public function viewFeed() {
    $contact_id = $this->_params['cid'] ?? NULL;
    $hash = $this->_params['key'] ?? NULL;

    // Ensure correct parameters.
    if (empty($contact_id) || empty($hash)) {
      return FALSE;
    }

    if (!_activityical_contact_has_feed_group($contact_id)) {
      return FALSE;
    }

    // Check $this->_params['key'] that it matches $this->_params['contact_id'].
    $feed = CRM_Activityical_Feed::getInstance($contact_id);
    if (!$feed->validateHash($hash)) {
      return FALSE;
    }
    return TRUE;
  }

  public function manageFeedDetails() {
    // Only allow access if no contact_id is given (working on my own contact)
    // or user has 'administer civicrm'.
    $contact_id = $this->_params['contact_id'] ?? NULL;
    return (
      !$contact_id
      || $contact_id == CRM_Core_Session::singleton()->getLoggedInContactID()
      || CRM_Core_Permission::check('administer CiviCRM')
    );
  }

}
