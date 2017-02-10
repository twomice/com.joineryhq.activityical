<?php

class CRM_Activityical_Feed {

  protected $contact_id;
  protected $timezone_string;
  private $query_params;
  private $hash;
  private static $instances = array();

  protected function  __construct($contact_id, $query_params = NULL) {
    $this->contact_id = $contact_id;
    $this->setQueryParams($query_params);
    $this->load();
  }

  public static function getInstance($contact_id = NULL, $query_params = NULL) {
    $contact_id = self::getContactId($contact_id);
    if (empty(self::$instances[$contact_id])) {
      $instance = new self($contact_id, $query_params);
      self::$instances[$contact_id] = $instance;
    }
    return self::$instances[$contact_id];
  }

  private function load() {
    $params = array(
      'contact_id' => $this->contact_id,
      'sequential' => 1,
    );
    $result = civicrm_api3('activityical_contact', 'get', $params);

    if ($result['count'] && $hash = CRM_Utils_Array::value('hash', $result['values'][0])) {
      $this->hash = $hash;
    }
    else {
      $this->generateHash();
    }
  }

  private function setQueryParams($query_params = NULL) {
    // No need to do this more than once per instance.
    if (!isset($this->query_params)) {
      $supported_params = array(
        'pdays' => 'integer',
        'fdays' => 'integer',
        'nocache' => 'integer',
      );
      if ($query_params === NULL) {
        $params = array_intersect_key($_GET, $supported_params);
        foreach ($params as $key => $value) {
          $type = $supported_params[$key];
          settype($value, $type);
          $this->query_params[$key] = $value;
        }
      }
    }
  }

  private static function getContactId($contact_id = NULL) {
    if (!empty($contact_id) && is_numeric($contact_id)) {
      return $contact_id;
    }
    else {
      return CRM_Core_Session::singleton()->getLoggedInContactID();
    }
  }

  public function generateHash() {
    // Ensure we have permission to do this.
    $perm = CRM_Activityical_Permission::singleton(array('contact_id' => $this->contact_id));
    if (!$perm->manageFeedDetails()) {
      CRM_Utils_System::permissionDenied();
    }
    $hash = md5(mt_rand(0, 10000000) . microtime());

    $params = array(
      'contact_id' => $this->contact_id,
    );
    $result = civicrm_api3('activityical_contact', 'get', $params);
    $id = CRM_Utils_Array::value('id', $result);

    $params = array(
      'id' => $id,
      'contact_id' => $this->contact_id,
      'hash' => $hash,
    );
    $result = civicrm_api3('activityical_contact', 'create', $params);

    $this->hash = $hash;
  }

  public function validateHash($hash) {
    $params = array(
      'contact_id' => $this->contact_id,
      'hash' => $hash,
    );
    $result = civicrm_api3('activityical_contact', 'get', $params);
    return (bool) $result['count'];
  }

  public function getUrl() {
    $url_query = array(
      'cid'=> $this->contact_id,
      'key' => $this->getHash(),
    );
    $url = CRM_Utils_System::url('civicrm/activityical/feed', $url_query, TRUE, NULL, FALSE, TRUE);
    return $url;
  }

  public function getHash() {
    if (empty($this->hash)) {
      $this->generateHash();
    }
    return $this->hash;
  }

  public function getData() {
    $return = array();

    // Retreive relevant extension settings.
    $api_params = array(
      'return' => array(
        'activityical_description_append_targets',
        'activityical_description_append_assignees',
        'activityical_past_days',
        'activityical_future_days',
        'activityical_activity_type_ids',
        'activityical_activity_status_ids',
      ),
    );
    $result = civicrm_api3('setting', 'get', $api_params);
    $settings = $result['values'][CRM_Core_Config::domainID()];

    // Set up placeholders for CiviCRM query. CiviCRM's query method doesn't
    // have anything like Drupals db_placeholders, so we do it ourselves here.
    $placeholders = $params = array();
    $placeholders['status'] = array();
    $placeholder_count = 1;

    // Placeholders for blocked statuses
    // TODO: this should be a setting.
    $values = CRM_Core_OptionGroup::values('activity_status');
    $blocked_status_values = array_keys(array_intersect($values, self::getBlockedStatuses()));

    if (empty($blocked_status_values)) {
      $blocked_status_values[] = 0;
    }
    foreach ($blocked_status_values as $value) {
      $i = $placeholder_count++;
      $placeholders['status'][] = '%' . $i;
      $params[$i] = array(
        $value,
        'Integer',
      );
    }

    // Placeholder for contact_id
    $i = $placeholder_count++;
    $placeholders['contact_id'] = '%' . $i;
    $params[$i] = array(
      $this->contact_id,
      'Integer',
    );

    // Add limits for pdays/activityical_past_days
    if (is_array($this->query_params) && array_key_exists('pdays', $this->query_params)) {
      $activityical_past_days = $this->query_params['pdays'];
    }
    else {
      $activityical_past_days = CRM_Utils_Array::value('activityical_past_days', $settings, 0);
    }
    $i = $placeholder_count++;
    $placeholders['activityical_past_days'] = '%' . $i;
    $params[$i] = array(
      $activityical_past_days,
      'Integer',
    );

    // Add limits for fdays/activityical_future_days
    if (is_array($this->query_params) && array_key_exists('fdays', $this->query_params)) {
      $activityical_future_days = $this->query_params['fdays'];
    }
    else {
      $activityical_future_days = CRM_Utils_Array::value('activityical_future_days', $settings, 0);
    }
    $i = $placeholder_count++;
    $placeholders['activityical_future_days'] = '%' . $i;
    $params[$i] = array(
      $activityical_future_days,
      'Integer',
    );

    // Create a WHERE clause component for the 'activityical_activity_type_ids' setting.
    $extra_wheres = array();
    if (!empty($settings['activityical_activity_type_ids']) && is_array($settings['activityical_activity_type_ids'])) {
      $placeholders['activity_type_id'] = array();
      foreach ($settings['activityical_activity_type_ids'] as $activity_type_id) {
        $i = $placeholder_count++;
        $placeholders['activity_type_id'][] = '%' . $i;
        $params[$i] = array(
          $activity_type_id,
          'Integer',
        );
      }
      $extra_wheres[] = 'AND civicrm_activity.activity_type_id IN (' . implode(',', $placeholders['activity_type_id']) . ')';
    }
    if (!empty($settings['activityical_activity_status_ids']) && is_array($settings['activityical_activity_status_ids'])) {
      $placeholders['activity_status_id'] = array();
      foreach ($settings['activityical_activity_status_ids'] as $activity_status_id) {
        $i = $placeholder_count++;
        $placeholders['activity_status_id'][] = '%' . $i;
        $params[$i] = array(
          $activity_status_id,
          'Integer',
        );
      }
      $extra_wheres[] = 'AND civicrm_activity.status_id IN (' . implode(',', $placeholders['activity_status_id']) . ')';
    }
    $extra_where = implode("\n", $extra_wheres);

    $query = "
      SELECT
        contact_primary.id as contact_id,
        civicrm_activity.id,
        source.id AS source_id,
        source.display_name AS `source_display_name`,
        GROUP_CONCAT(
          DISTINCT
          other_assignee.display_name
          SEPARATOR '; '
        ) AS other_assignees,
        GROUP_CONCAT(
          DISTINCT
          target.display_name
          SEPARATOR '; '
        ) AS targets,
        civicrm_activity.activity_type_id,
        activity_type.label AS activity_type,
        civicrm_activity.subject AS activity_subject,
        civicrm_activity.activity_date_time AS activity_date_time,
        civicrm_activity.duration AS activity_duration,
        civicrm_activity.location AS activity_location,
        civicrm_activity.details AS activity_details
      FROM civicrm_contact contact_primary
        INNER JOIN civicrm_activity_contact activity_assignment
          ON (
            activity_assignment.contact_id = contact_primary.id
            AND activity_assignment.record_type_id = 1
          )
        INNER JOIN civicrm_activity
          ON (
            civicrm_activity.id = activity_assignment.activity_id
            AND civicrm_activity.is_deleted = 0
            AND civicrm_activity.is_current_revision = 1
          )
        INNER JOIN civicrm_activity_contact activity_source
          ON (
            activity_source.activity_id = civicrm_activity.id
            AND activity_source.record_type_id = 2
          )
        INNER JOIN civicrm_contact source ON source.id = activity_source.contact_id
        INNER JOIN civicrm_option_group option_group_activity_type
          ON (option_group_activity_type.name = 'activity_type')
        INNER JOIN civicrm_option_value activity_type
          ON (
            civicrm_activity.activity_type_id = activity_type.value
            AND option_group_activity_type.id = activity_type.option_group_id
          )
        LEFT JOIN civicrm_activity_contact other_activity_assignment
          ON (
            civicrm_activity.id = other_activity_assignment.activity_id
            AND other_activity_assignment.record_type_id = 1
          )
        LEFT JOIN civicrm_contact other_assignee
          ON other_activity_assignment.contact_id = other_assignee.id
          AND other_assignee.is_deleted = 0
          AND other_assignee.id <> contact_primary.id
        LEFT JOIN civicrm_activity_contact activity_target
          ON (
            civicrm_activity.id = activity_target.activity_id
            AND activity_target.record_type_id = 3
          )
        LEFT JOIN civicrm_contact target ON activity_target.contact_id = target.id
      WHERE
        civicrm_activity.status_id NOT IN
          (". implode(',', $placeholders['status']) . ")
        AND contact_primary.id = '{$placeholders['contact_id']}'
        AND civicrm_activity.is_test = 0
        AND date(civicrm_activity.activity_date_time) >= (CURRENT_DATE - INTERVAL {$placeholders['activityical_past_days']} DAY)
        AND date(civicrm_activity.activity_date_time) <= (CURRENT_DATE + INTERVAL {$placeholders['activityical_future_days']} DAY)
        $extra_where
      GROUP BY civicrm_activity.id
      ORDER BY activity_date_time desc
    ";

    $dao = CRM_Core_DAO::executeQuery($query, $params);
    while ($dao->fetch()) {
      $row = $dao->toArray();

      $description = array();
      if ($row['activity_details']) {
        $description[] = preg_replace('/(\n|\r)/', '', $row['activity_details']);
      }

      if (!empty($settings['activityical_description_append_targets']) && $row['targets']) {
        $description[] = 'With: '. $row['targets'];
      }
      if (!empty($settings['activityical_description_append_assignees']) && $row['other_assignees']) {
        $description[] = 'Other assignees: '. $row['other_assignees'];
      }
      $row['description'] = implode("\n", $description);

      // FIXME: how to handle timezones?
      // $row['activity_date_time'] = civicrm_activity_contact_datetime_to_utc($row['activity_date_time'], $this->contact_id);

      $return[] = $row;
    }
    return $return;
  }

  public function getContents() {
    if ($this->useCache()) {
      $cache = new CRM_Activityical_Cache($this->contact_id);
      $cache_value = $cache->retrieve();
      if (empty($cache_value)) {
        $cache->store($this->getFeed());
      }
      return $cache->retrieve();
    }
    else {
      return $this->getFeed();
    }
  }

  private function useCache() {
    $use_cache = TRUE;
    $this->setQueryParams();
    if (!empty($this->query_params['nocache']) && $this->query_params['nocache'] == 1) {
      $use_cache = FALSE;
    }
    else {
      // Check if we're configured to use caching.
      $api_params = array(
        'return' => array(
          'activityical_cache_lifetime',
        ),
      );
      $result = civicrm_api3('setting', 'get', $api_params);
      $use_cache = (bool)CRM_Utils_Array::value('activityical_cache_lifetime', $result['values'][CRM_Core_Config::domainID()], 0);
    }
    return $use_cache;
  }

  public function getFeed() {
    $activities = $this->getData();
    foreach ($activities as &$activity) {
      // Define URL to activity.
      $path = "civicrm/activity?action=view&context=activity&reset=1&cid={$activity['contact_id']}&id={$activity['id']}&atype={$activity['activity_type_id']}";
      $activity['url'] = CRM_Utils_System::url($path, NULL, TRUE, NULL, FALSE, FALSE, TRUE);

      // Adjust date/time for relevant timezone.
      $activity['activity_date_time'] = $this->convertToUTC($activity['activity_date_time']);
    }

    // Require a file from CiviCRM's dynamic include path.
    require_once 'CRM/Core/Smarty.php';
    $tpl = CRM_Core_Smarty::singleton();
    $tpl->assign('activities', $activities);

    // Assign base_url to be used in links.
    global $base_url;
    $tpl->assign('base_url', $base_url);

    // Calculate and assign the domain for activity uids
    $domain = parse_url('http://'. $_SERVER['HTTP_HOST'], PHP_URL_HOST);
    $tpl->assign('domain', $domain);

    $output = $tpl->fetch('CRM/Activityical/snippet/Feed.tpl');

    // Ensure CRLF line endings. I'm not willing to trust the line endings in
    // Feed.tpl, so we specifically enforce CRLF here.
    // Reference: http://icalendar.org/iCalendar-RFC-5545/3-1-content-lines.html
    $output = preg_replace('/(\r\n|\n)/', "\r\n", $output);

    // Fold any remaining long lines. (The smarty modifier crmICalText is used
    // in Feed.tpl, and makes a noble effort, but isn't applied to everything,
    // and is a bit of sledgehammer by wrapping at 50 characters).
    // Reference: http://icalendar.org/iCalendar-RFC-5545/3-1-content-lines.html
    $output = implode("\r\n ", str_split($output, 74));
    
    return $output;
  }

  public static function getBlockedStatuses() {
    $blocked_statuses = array(
      'Completed',
      'Cancelled',
      'Left Message',
      'Unreachable',
      'Not Required',
    );
    return $blocked_statuses;
  }

  public function getTimezoneString() {
    if (empty($this->timezone_string)) {
      $timezone_string = '';
      $method_name = 'getTimezoneString_' . CIVICRM_UF;
      if (method_exists(__CLASS__, $method_name)) {
        $timezone_string = $this->$method_name();
      }
      $timezone_string = $timezone_string ?: 'UTC';
      $this->timezone_string = $timezone_string;
    }
    return $this->timezone_string;
  }

  public function getTimezoneString_WordPress() {
    $timezone_string = '';
    if (function_exists('get_option')) {
      $timezone_string = get_option('timezone_string');
    }
    return $timezone_string;
  }

  public function getTimezoneString_Drupal() {
    $timezone_string = '';
    // If timezones can be configurable per user, get the user's timezone setting.
    if (variable_get('configurable_timezones', 1)) {
      // Get the global user if no uid is given.
      $result = civicrm_api3('UFMatch', 'get', array(
        'sequential' => 1,
        'contact_id' => $this->contact_id,
      ));
      if (!empty($result['values'])) {
        $uid = $result['values'][0]['uf_id'];
        $user = user_load($uid);
        // Get the user's timezone setting, if any.
        if ($user->uid && $user->timezone) {
          $timezone_string = $user->timezone;
        }
      }
    }
    // If no timezone has been found yet, get the system timezone.
    if (empty($timezone_string)) {
      // Use @ operator to ignore PHP strict notice if time zone has not yet been
      // set in the php.ini configuration.
      $timezone_string = variable_get('date_default_timezone', @date_default_timezone_get());
    }
    return $timezone_string;
  }

  public function getTimezoneString_Joomla() {
    $timezone_string = '';
    $result = civicrm_api3('UFMatch', 'get', array(
      'sequential' => 1,
      'contact_id' => $this->contact_id,
    ));
    if (!empty($result['values'])) {
      $uid = $result['values'][0]['uf_id'];
      $user = JFactory::getUser($uid);
      $timezone_string = $user->getParam('timezone', '');
    }
    if (empty($timezone_string)) {
      $timezone_string = JFactory::getConfig()->get('offset');
    }
    return $timezone_string;
  }

  public function convertToUTC($datetime, $timezone_string = '') {
    $timezone_string = $this->getTimezoneString();
    $converted_time = new DateTime($datetime, new DateTimeZone($timezone_string) );
    $converted_time->setTimeZone(new DateTimeZone('UTC'));
    return $converted_time->format('Y-m-d H:i:s');
  }
}
