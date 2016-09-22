<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class CRM_Activityical_Permission {

  /**
   * We only need one instance of this object. So we use the singleton
   * pattern and cache the instance in this variable
   *
   * @var CRM_Core_Config
   */
  private static $_singleton = NULL;

  /**
   * Parameters for permissioning, defaults to $_GET
   * @var Array
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
   * @param bool $loadFromDB
   *   whether to load from the database.
   * @param bool $force
   *   whether to force a reconstruction.
   *
   * @return CRM_Core_Config
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
    $contact_id = CRM_Utils_Array::value('cid', $this->_params);
    $hash = CRM_Utils_Array::value('key', $this->_params);

    // Ensure correct parameters.
    if (empty($contact_id) || empty($hash)) {
      return FALSE;
    }
    
    // Check $this->_params['contact_id'] that they have the right civicrm group.
    $existing = civicrm_api3('setting', 'get', array('return' => 'activityical_group_id'));
    $domainID = CRM_Core_Config::domainID();
    $group_id = $existing['values'][$domainID]['activityical_group_id'];
    if (empty($group_id)) {
      // No group defined; nobody can be in an undefined group.
      return FALSE;
    }
    $api_params = array (
      'group_id' => $group_id,
      'contact_id' => $contact_id,
    );
    $result = civicrm_api3('group_contact', 'get', $api_params);
    if (!$result['count']) {
      return FALSE;
    }

    // Check $this->_params['key'] that it matches $this->_params['contact_id'].
    $feed = new CRM_Activityical_Feed($contact_id);
    if (!$feed->validateHash($hash)) {
      return FALSE;
    }
    return TRUE;
  }

  public function manageFeedDetails() {
    // Only allow access if no contact_id is given (working on my own contact)
    // or user has 'administer civicrm'.
    return (
      CRM_Utils_Array::value('contact_id', $this->_params)
      || CRM_Core_Permission::check('administer CiviCRM')
    );
  }
}