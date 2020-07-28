<?php

class CRM_Activityical_BAO_ActivityicalCache extends CRM_Activityical_DAO_ActivityicalCache {

  /**
   * Create a new ActivityicalCache based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Activityical_DAO_ActivityicalCache|NULL
   */
  // public static function create($params) {
  //   $className = 'CRM_Activityical_DAO_ActivityicalCache';
  //   $entityName = 'ActivityicalCache';
  //   $hook = empty($params['id']) ? 'create' : 'edit';

  //   CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
  //   $instance = new $className();
  //   $instance->copyValues($params);
  //   $instance->save();
  //   CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

  //   return $instance;
  // }
}
