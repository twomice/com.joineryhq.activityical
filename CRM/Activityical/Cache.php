<?php

class CRM_Activityical_Cache {

  protected $contact_id;
  protected $cache = '';
  protected $loaded = FALSE;

  public function  __construct($contact_id) {
    $this->contact_id = $contact_id;
  }

  private function load($force_load = FALSE) {
    if (!$this->loaded || $force_load) {
      $params = array(
        'contact_id' => $this->contact_id,
        'sequential' => TRUE,
      );
      // Never load expired data.
      if ($min_cached_timestamp = self::getMinCacheTimestamp()) {
        $params['cached'] = array('>=' => $min_cached_timestamp);
      }
      $result = _activityical_civicrmapi('activityical_cache', 'get', $params);
      if ($result['count']) {
        $this->cache = CRM_Utils_Array::value('cache', $result['values'][0], '');
      }
      $this->loaded = TRUE;
    }
  }

  public function retrieve() {
    $this->load();
    return $this->cache;
  }

  public function clear() {
    $params = array(
      'contact_id' => $this->contact_id,
    );
    $result = _activityical_civicrmapi('activityical_cache', 'get', $params);
    $id = CRM_Utils_Array::value('id', $result);

    if ($id) {
      $params = array(
        'id' => $result['id'],
      );
      _activityical_civicrmapi('activityical_cache', 'delete', $params);
    }
  }

  public static function clearAll() {
    _activityical_civicrmapi('activityical_cache', 'clearall', array());
  }

  public function store($cache) {
    $params = array(
      'contact_id' => $this->contact_id,
    );
    $result = _activityical_civicrmapi('activityical_cache', 'get', $params);

    $params = array(
      'id' => CRM_Utils_Array::value('id', $result),
      'contact_id' => $this->contact_id,
      'cache' => $cache,
    );
    _activityical_civicrmapi('activityical_cache', 'create', $params);

    $this->cache = $cache;
  }

  public static function getMinCacheTimestamp() {
    // Get configured max cache lifetime (in minutes).
    $api_params = array(
      'return' => array(
        'activityical_cache_lifetime',
      ),
    );
    $result = _activityical_civicrmapi('setting', 'get', $api_params);
    if ($cache_lifetime_minutes = CRM_Utils_Array::value('activityical_cache_lifetime', $result['values'][CRM_Core_Config::domainID()], 0)) {
      $time = time();
      $min_cache_time = $time - ($cache_lifetime_minutes * 60);
      return date('Y-m-d H:i:s', $min_cache_time);
    }
    else {
      return FALSE;
    }
  }

}
