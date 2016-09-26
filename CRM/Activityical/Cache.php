<?php

class CRM_Activityical_Cache {


  protected $contact_id;
  protected $cache = '';
  protected $loaded = FALSE;

  function  __construct($contact_id) {
    $this->contact_id = $contact_id;
  }

  private function load($force_load = FALSE) {
    if (!$this->loaded || $force_load) {
      $params = array(
        'contact_id' => $this->contact_id,
        'sequential' => TRUE,
      );
      $result = civicrm_api3('activityical_cache', 'get', $params);
      if ($result['count']) {
        $this->cache = CRM_Utils_Array::value('cache', $result['values'][0], '');
      }
      $this->loaded = TRUE;
    }
  }

  function retrieve() {
    $this->load();
    return $this->cache;
  }

  function clear() {
    $params = array(
      'contact_id' => $this->contact_id,
    );
    $result = civicrm_api3('activityical_cache', 'get', $params);
    $id = CRM_Utils_Array::value('id', $result);

    if ($id) {
      $params = array(
        'id' => $result['id'],
      );
      civicrm_api3('activityical_cache', 'delete', $params);
    }
  }

  function store($cache) {
    $params = array(
      'contact_id' => $this->contact_id,
    );
    $result = civicrm_api3('activityical_cache', 'get', $params);

    $params = array(
      'id' => CRM_Utils_Array::value('id', $result),
      'contact_id' => $this->contact_id,
      'cache' => $cache,
    );
    civicrm_api3('activityical_cache', 'create', $params);

    $this->cache = $cache;
  }
}