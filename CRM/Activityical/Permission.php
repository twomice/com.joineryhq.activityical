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
    dsm('FIXME: '. __CLASS__ . '::' . __METHOD__ .' always returns TRUE.');
    dsm($this->_params, 'params');
    // TODO: check $this->_params['cid'] that they have the right civicrm group.
    // TODO: check $this->_params['hash'] that it matches $this->_params['cid'].
    return TRUE;
  }

  public function viewFeedDetails() {
    dsm('FIXME: '. __CLASS__ . '::' . __METHOD__ .' always returns TRUE.');
    dsm($this->_params, 'params');

    // TODO: $contact_id = ($this->_params['cid'] || current user's cid)
    // 
    // TODO: check that user has 'access civicrm'
    // TODO: check that user has 'administer civicrm'
    //    OR that $contact_id matches user's cid
    return TRUE;
  }
}