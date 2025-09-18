<?php

require_once 'activityical.civix.php';
use CRM_Activityical_ExtensionUtil as E;

/**
 * Custom permissions checking for this extension.
 *
 * @param array $access_arguments as defined in menu xml
 * @param string $op "or" if xml <access_arguments> is comma-delimited; "and" it
 *   it is semicolon-delimited.
 * @return bool
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
  $extRoot = dirname(__FILE__) . DIRECTORY_SEPARATOR;
  $template =& CRM_Core_Smarty::singleton();
  $template->plugins_dir = array_merge(array($extRoot . 'Smarty' . DIRECTORY_SEPARATOR . 'plugins'), (array) $template->plugins_dir);

  _activityical_civix_civicrm_config($config);
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
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function activityical_civicrm_enable() {
  _activityical_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function activityical_civicrm_navigationMenu(&$menu) {
  _activityical_get_max_navID($menu, $max_navID);
  _activityical_civix_insert_navigation_menu($menu, 'Administer/System Settings', array(
    'label' => E::ts('Activity iCalendar Feed', array('domain' => 'com.joineryhq.activityical')),
    'name' => 'Activity iCalendar Feed',
    'url' => 'civicrm/admin/activityical/settings',
    'permission' => 'administer CiviCRM',
    'operator' => 'AND',
    'separator' => NULL,
    'navID' => ++$max_navID,
  ));
  _activityical_civix_navigationMenu($menu);
}

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
      if (isset($tpl->getTemplateVars()['activity_rows']) || isset($tpl->getTemplateVars()['activity_rowsEmpty'])) {
        $url_query = array(
          'contact_id' => $contact_id,
        );
        $feed_details_url = CRM_Utils_System::url('civicrm/activityical/details', $url_query, TRUE, NULL, FALSE);
        CRM_Core_Session::setStatus(ts('Assigned activities are accessible as an iCalendar feed.') . ' ' . '<a href="' . $feed_details_url . '">' . E::ts('Feed details...') . '</a>');
      }
    }
  }

  if (!empty($_GET['snippet']) && $_GET['snippet'] == 'json' && $page_name == 'CRM_Activity_Page_Tab') {
    if (implode('/', $page->urlPath) == 'civicrm/contact/view/activity') {
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
        'contact_id' => $contact_id,
      );
      $feed_details_url = CRM_Utils_System::url('civicrm/activityical/details', $url_query, TRUE, NULL, FALSE);
      $tpl->assign('contact_id', $contact_id);

      // Get the feed URL for this contact.
      $feed = CRM_Activityical_Feed::getInstance($contact_id);
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
      $resource->addStyleFile('com.joineryhq.activityical', 'css/extension.css');
    }
  }
}

/**
 * Check whether a contact is in the group which means
 * they should have a feed
 * @return bool
 */
function _activityical_contact_has_feed_group($contact_id): bool {
  $group_id = \Civi::settings()->get('activityical_group_id');
  if (empty($group_id)) {
    // No group defined; nobody can be in an undefined group.
    return FALSE;
  }

  // will return contact if they are in the group, or null if not
  // Api4 should handle smart groups seamlessly too
  $contactInGroup = \Civi\Api4\Contact::get(FALSE)
    ->addSelect('id')
    ->addWhere('id', '=', $contact_id)
    ->addWhere('groups', 'IN', [$group_id])
    ->execute()
    ->first();

  return !!$contactInGroup;
}

/**
 * Implements hook_civicrm_pre().
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
      $result = _activityical_civicrmapi('activity_contact', 'get', $api_params);
      foreach ($result['values'] as $value) {
        $contact_ids[$value['contact_id']] = 1;
      }
      foreach (CRM_Utils_Array::value('assignee_contact_id', $params, array()) as $contact_id) {
        $contact_ids[$contact_id] = 1;
      }
      foreach (array_keys($contact_ids) as $contact_id) {
        $cache = new CRM_Activityical_Cache($contact_id);
        $cache->clear();
      }
    }
  }
}

/**
 * Implements hook_civicrm_post().
 */
function activityical_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  if ($objectName == 'Activity' && $op == 'create') {
    // If we've created an activity, clear activityical cache for any assignees.
    $contact_ids = array();
    $api_params = array(
      'activity_id' => $objectId,
      'record_type_id' => 1,
    );
    $result = _activityical_civicrmapi('activity_contact', 'get', $api_params);
    foreach ($result['values'] as $value) {
      $cache = new CRM_Activityical_Cache($value['contact_id']);
      $cache->clear();
    }
  }
}

/**
 * For an array of menu items, recursively get the value of the greatest navID
 * attribute.
 * @param <type> $menu
 * @param <type> $max_navID
 */
function _activityical_get_max_navID(&$menu, &$max_navID = NULL) {
  foreach ($menu as $id => $item) {
    if (!empty($item['attributes']['navID'])) {
      $max_navID = max($max_navID, $item['attributes']['navID']);
    }
    if (!empty($item['child'])) {
      _activityical_get_max_navID($item['child'], $max_navID);
    }
  }
}

/**
 * Log CiviCRM API errors to CiviCRM log.
 */
function _activityical_log_api_error(CiviCRM_API3_Exception $e, string $entity, string $action, array $params) {
  $message = "CiviCRM API Error '{$entity}.{$action}': " . $e->getMessage() . '; ';
  $message .= "API parameters when this error happened: " . json_encode($params) . '; ';
  $bt = debug_backtrace();
  $error_location = "{$bt[1]['file']}::{$bt[1]['line']}";
  $message .= "Error API called from: $error_location";
  CRM_Core_Error::debug_log_message($message);
}

/**
 * CiviCRM API wrapper. Wraps with try/catch, redirects errors to log, saves
 * typing.
 */
function _activityical_civicrmapi(string $entity, string $action, array $params, bool $silence_errors = TRUE) {
  try {
    $result = civicrm_api3($entity, $action, $params);
  }
  catch (CiviCRM_API3_Exception $e) {
    _activityical_log_api_error($e, $entity, $action, $params);
    if (!$silence_errors) {
      throw $e;
    }
  }

  return $result;
}

/**
 * Implements hook_civicrm_tokens().
 */
function activityical_civicrm_tokens(&$tokens) {
  $tokens['activityical'] = [
    'activityical.url' => 'Activityical Feed URL',
  ];
}

/**
 * Implements hook_civicrm__tokenValues().
 */
function activityical_civicrm_tokenValues(&$values, $cids, $job = NULL, $tokens = [], $context = NULL) {
  if (isset($tokens['activityical'])) {
    if (!(array_key_exists('url', $tokens['activityical']) || in_array('url', $tokens['activityical']))) {
      return;
    }

    foreach ($cids as $cid) {
      $feed_link = CRM_Activityical_Feed::getInstance($cid);
      $values[$cid]['activityical.url'] = $feed_link->getUrl();
    }
  }
}

/**
 * Implements hook_civicrm_merge().
 */
function activityical_civicrm_merge($type, &$data, $mainId = NULL, $otherId = NULL, $tables = NULL) {
  if ($type == 'sqls') {
    // Set Query using composeQuery method
    $deleteInIcalCache = CRM_Core_DAO::composeQuery(
      "DELETE FROM civicrm_activityicalcache WHERE contact_id IN (%1, %2)",
      [
        1 => [$mainId, 'Integer'],
        2 => [$otherId, 'Integer'],
      ]
    );
    $deleteInIcalContact = CRM_Core_DAO::composeQuery("DELETE FROM civicrm_activityicalcontact WHERE contact_id = %1", [1 => [$otherId, 'Integer']]);

    // Insert the two query in the $data array
    array_unshift($data, $deleteInIcalCache);
    array_unshift($data, $deleteInIcalContact);
  }
}
