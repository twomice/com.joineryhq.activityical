<?php

class CRM_Activityical_BAO_ActivityicalContact extends CRM_Activityical_DAO_ActivityicalContact {

  /**
   * Create a new ActivityicalContact based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Activityical_DAO_ActivityicalContact|NULL
   */
  // public static function create($params) {
  //   $className = 'CRM_Activityical_DAO_ActivityicalContact';
  //   $entityName = 'ActivityicalContact';
  //   $hook = empty($params['id']) ? 'create' : 'edit';

  //   CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
  //   $instance = new $className();
  //   $instance->copyValues($params);
  //   $instance->save();
  //   CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

  //   return $instance;
  // }
}
