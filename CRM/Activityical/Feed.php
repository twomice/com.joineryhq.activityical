<?php

class CRM_Activityical_Feed {
  
  protected $contact_id;

  private $url;

  private $hash;

  function  __construct($contact_id = NULL) {
    $this->setContactId($contact_id);
    $this->load();
  }

  private function load() {
    $dao = new CRM_Activityical_DAO_ActivityicalContact();
    $dao->contact_id = $this->contact_id;
    $dao->find();
    $dao->fetch();

    if (!empty($dao->hash)) {
      $this->hash = $dao->hash;
    }
    else {
      $this->generateHash();
    }
  }

  private function setContactId($contact_id = NULL) {
    if (!empty($contact_id) && is_numeric($contact_id)) {
      $this->contact_id = $contact_id;
    }
    else {
      $this->contact_id = CRM_Core_Session::singleton()->getLoggedInContactID();
    }
  }

  public function generateHash() {
    // Ensure we have permission to do this.
    $perm = CRM_Activityical_Permission::singleton(array('contact_id' => $this->contact_id));
    if (!$perm->manageFeedDetails()) {
      CRM_Utils_System::permissionDenied();
    }
    $hash = md5(mt_rand(0, 10000000) . microtime());
    // FIXME: use API here instead of DAO.
    $dao = new CRM_Activityical_DAO_ActivityicalContact();
    $dao->contact_id = $this->contact_id;
    $dao->find();
    $dao->fetch();
    $dao->hash = $hash;
    if ($dao->id) {
      $dao->update();
    }
    else {
      $dao->save();
    }
    $this->hash = $hash;
  }

  public function validateHash($hash) {
    // FIXME: use API here instead of DAO.
    $dao = new CRM_Activityical_DAO_ActivityicalContact();
    $dao->contact_id = $this->contact_id;
    $dao->hash = $hash;
    $dao->find();
    return (bool) $dao->N;
  }

  public function getUrl() {
    $url_query = array(
      'cid'=> $this->contact_id,
      'key' => $this->hash,
    );
    $url = CRM_Utils_System::url('civicrm/activityical/feed', $url_query, TRUE, NULL, FALSE);
    return $url;
  }

  public function getData() {
    $return = array();

    // Set up placeholders for CiviCRM query. CiviCRM's query method doesn't
    // have anything like Drupals db_placeholders, so we do it ourselves here.
    $placeholders = $params = array();
    $placeholders['status'] = array();
    $placeholder_count = 1;

    // Placeholders for blocked statuses
    // TODO: this should be a setting.
    $values = CRM_Core_OptionGroup::values('activity_status');
    $blocked_statuses = array(
      'Completed',
      'Cancelled',
      'Left Message',
      'Unreachable',
      'Not Required',
    );
    $blocked_status_values = array_keys(array_intersect($values, $blocked_statuses));

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

      $settings = array('activityical_description_append_targets', 'activityical_description_append_assignees');
      $result = civicrm_api3('setting', 'get', array('return' => $settings));
      $domainID = CRM_Core_Config::domainID();
      foreach ($settings as $setting) {
        $$setting = CRM_Utils_Array::value($setting, $result['values'][$domainID]);
      }

      if ($activityical_description_append_targets && $row['targets']) {
        $description[] = 'With: '. $row['targets'];
      }
      if ($activityical_description_append_assignees && $row['other_assignees']) {
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
    // TODO: add caching.

    // Require a file from CiviCRM's dynamic include path.
    require_once 'CRM/Core/Smarty.php';
    $tpl = CRM_Core_Smarty::singleton();
    $tpl->assign('activities', $this->getData());

    // Assign base_url for to be used in links.
    global $base_url;
    $tpl->assign('base_url', $base_url);

    // Calculate and assign the domain for activity uids
    $domain = parse_url('http://'. $_SERVER['HTTP_HOST'], PHP_URL_HOST);
    $tpl->assign('domain', $domain);

    $output = $tpl->fetch('CRM/Activityical/snippet/Feed.tpl');
    return $output;
  }

}
