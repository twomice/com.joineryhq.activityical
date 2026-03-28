<?php

/**
 * ActivityicalCache.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_activityical_cache_create_spec(&$spec) {
  // $spec['some_parameter']['api.required'] = 1;
}

/**
 * ActivityicalCache.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws CRM_Core_Exception
 */
function civicrm_api3_activityical_cache_create($params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * ActivityicalCache.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws CRM_Core_Exception
 */
function civicrm_api3_activityical_cache_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * ActivityicalCache.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws CRM_Core_Exception
 */
function civicrm_api3_activityical_cache_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * ActivityicalCache.clearall API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws CRM_Core_Exception
 */
function civicrm_api3_activityical_cache_clearall($params) {
  $dao_name = _civicrm_api3_activityical_cache_DAO();
  $dao = new $dao_name();
  $dao->whereAdd('1');
  $result = $dao->delete(DB_DATAOBJECT_WHEREADD_ONLY);
  if ($result === FALSE) {
    throw new CRM_Core_Exception('Could not delete all cache entries.');
  }
  return civicrm_api3_create_success($result, array(), 'activityical_cach', 'clearall');
}

/**
 * Get DAO name
 */
function _civicrm_api3_activityical_cache_DAO() {
  return 'CRM_Activityical_DAO_ActivityicalCache';
}
