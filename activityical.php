<?php

require_once 'activityical.civix.php';


/**
 * Custom permissions checking for this extension.
 * 
 * @param Array $access_arguments as defined in menu xml
 * @param String $op "or" if xml <access_arguments> is comma-delimited; "and" it
 *   it is semicolon-delimited.
 * @return Boolean
 */
function _activityical_check_permission($access_arguments, $op) {
  $checker = CRM_Activityical_Permission::singleton();
  if ($op == 'or') {
    foreach ($access_arguments as $method) {
      if ($checker->$method()) {
        return TRUE;
      }
    }
    return FALSE;
  }
  elseif ($op == 'and') {
    foreach ($access_arguments as $method) {
      if (!$checker->$method()) {
        return FALSE;
      }
    }
    return TRUE;
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function activityical_civicrm_config(&$config) {
  _activityical_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param array $files
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function activityical_civicrm_xmlMenu(&$files) {
  _activityical_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function activityical_civicrm_install() {
  _activityical_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function activityical_civicrm_uninstall() {
  _activityical_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function activityical_civicrm_enable() {
  _activityical_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function activityical_civicrm_disable() {
  _activityical_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function activityical_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _activityical_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function activityical_civicrm_managed(&$entities) {
  _activityical_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * @param array $caseTypes
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function activityical_civicrm_caseTypes(&$caseTypes) {
  _activityical_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function activityical_civicrm_angularModules(&$angularModules) {
_activityical_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function activityical_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _activityical_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function activityical_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function activityical_civicrm_navigationMenu(&$menu) {
  _activityical_civix_insert_navigation_menu($menu, 'Administer/System Settings', array(
    'label' => ts('Activity iCalendar Feed', array('domain' => 'com.joineryhq.activityical')),
    'name' => 'Activity iCalendar Feed',
    'url' => 'civicrm/admin/activityical/settings',
    'permission' => 'administer CiviCRM',
    'operator' => 'AND',
    'separator' => NULL,
  ));
  _activityical_civix_navigationMenu($menu);
} // */


/**
 * Implements hook_civicrm_pageRun().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_pageRun
 */
function activityical_civicrm_pageRun(&$page) {
  $page_name = $page->getVar('_name');

  // Conditionally add a status message pointing to the activities iCal feed.
  // Only on the user dashboard, and only if the current user has permissions
  if ($page_name == 'CRM_Contact_Page_View_UserDashBoard') {
    $contact_id = $page->_contactId;

    if (_activityical_contact_has_feed_group($contact_id)) {
      $tpl = CRM_Core_Smarty::singleton();
      // Only if this CiviCRM is showing activities on the user dashboard
      if (isset($tpl->_tpl_vars['activity_rows']) || isset($tpl->_tpl_vars['activity_rowsEmpty'])) {
        $url_query = array(
          'contact_id'=> $contact_id,
        );
        $feed_details_url = CRM_Utils_System::url('civicrm/activityical/details', $url_query, TRUE, NULL, FALSE);
        CRM_Core_Session::setStatus(ts('Assigned activities are accessible as an iCalendar feed.') . ' '. '<a href="'. $feed_details_url . '">'. ts('Feed details...') . '</a>');
      }
    }
  }

  if (!empty($_GET['snippet']) && $_GET['snippet'] == 'json' && $page_name == 'CRM_Activity_Page_Tab') {
    if(implode('/', $page->urlPath) == 'civicrm/contact/view/activity') {
      // Do this only on the contact Activities tab.

      $contact_id = $page->_contactId;
      if (!_activityical_contact_has_feed_group($contact_id)) {
        // Contact cannot have a feed. We'll add no links. Just return.
        return;
      }

      // Prepare to parse a Smarty template.
      $tpl = CRM_Core_Smarty::singleton();

      // Figure out if we should display the "details" link.
      if (
        CRM_Core_Permission::check('administer CiviCRM')
        || $contact_id == CRM_Core_Session::singleton()->getLoggedInContactID()
      ) {
        $tpl->assign('access_details', TRUE);
      }

      // Get the feed details URL for this contact.
      $url_query = array(
        'contact_id'=> $contact_id,
      );
      $feed_details_url = CRM_Utils_System::url('civicrm/activityical/details', $url_query, TRUE, NULL, FALSE);
      $tpl->assign('contact_id', $contact_id);

      // Get the feed URL for this contact.
      $feed = new CRM_Activityical_Feed($contact_id);
      $tpl->assign('feed_url', $feed->getUrl());

      // Render the template.
      $snippet = $tpl->fetch('CRM/Activityical/snippet/ActivitiesTabExtra.tpl');

      // Add JS and CSS to insert the renered template into the Activities tab.
      $vars = array(
        'snippet' => $snippet,
      );
      $resource = CRM_Core_Resources::singleton();
      $resource->addVars('activityical', $vars);
      $resource->addScriptFile('com.joineryhq.activityical', 'js/actiivtyical_activities_tab.js');
      $resource->addStyleFile('com.joineryhq.activityical', 'css/activityical.css');
    }
  }
}

function _activityical_contact_has_feed_group($contact_id) {
  // Check $this->_params['contact_id'] that they have the right civicrm group.
  $result = civicrm_api3('setting', 'get', array('return' => 'activityical_group_id'));
  $domainID = CRM_Core_Config::domainID();
  $group_id = $result['values'][$domainID]['activityical_group_id'];
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

  return TRUE;
}

/**
 * Implements hook_civicrm_entityTypes.
 */
function activityical_civicrm_entityTypes(&$entityTypes) {
 $entityTypes['CRM_Activityical_DAO_ActivityicalContact'] = array(
   'name' => 'ActivityicalContact',
   'class' => 'CRM_Activityical_DAO_ActivityicalContact',
   'table' => 'civicrm_activityicalcontact',
 );
 $entityTypes['CRM_Activityical_DAO_ActivityicalCache'] = array(
   'name' => 'ActivityicalCache',
   'class' => 'CRM_Activityical_DAO_ActivityicalCache',
   'table' => 'civicrm_activityicalcache',
 );
}

/**
 * Implements hook_civicrm_pre.
 */
function activityical_civicrm_pre($op, $objectName, $objectId, &$params) {
  if ($objectName == 'Activity' && (
    $op == 'edit'
    || $op == 'delete'
  )) {
    // If we're changing an activity, clear activityical cache for any new or
    // old assignees.
    $id = $objectId ?: CRM_Utils_Array::value('id', $params);
    if ($id) {
      $contact_ids = array();
      $api_params = array(
        'activity_id' => $id,
        'record_type_id' => 1,
      );
      $result = civicrm_api3('activity_contact', 'get', $api_params);
      foreach ($result['values'] as $value) {
        $contact_ids[$value['contact_id']] = 1;
      }
      foreach (CRM_Utils_Array::value('assignee_contact_id', $params) as $contact_id) {
        $contact_ids[$contact_id] = 1;
      }
      foreach(array_keys($contact_ids) as $contact_id) {
        $cache = new CRM_Activityical_Cache($contact_id);
        $cache->clear();
      }
    }
  }
}

/**
 * Implements hook_civicrm_post.
 */
function activityical_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  if ($objectName == 'Activity' && $op == 'create') {
    // If we've created an activity, clear activityical cache for any assignees.
    $contact_ids = array();
    $api_params = array(
      'activity_id' => $objectId,
      'record_type_id' => 1,
    );
    $result = civicrm_api3('activity_contact', 'get', $api_params);
    foreach ($result['values'] as $value) {
      $cache = new CRM_Activityical_Cache($value['contact_id']);
      $cache->clear();
    }
  }
}