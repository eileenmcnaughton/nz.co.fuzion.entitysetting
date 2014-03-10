<?php

/**
 * EntitySetting.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_entity_setting_create_spec(&$spec) {
  $spec['entity_id']['api.required'] = 1;
  $spec['entity_type']['api.required'] = 1;
  $spec['settings'] = array(
   'title' => 'Settings Data',
  );
  $spec['key'] = array(
    'api.required' => TRUE,
    'title' => 'Setting NameSpace - You pass the settings for one key'
  );
}

/**
 * EntitySetting.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_entity_setting_create($params) {
  return _civicrm_api3_basic_create('CRM_Entitysetting_BAO_EntitySetting', $params);
}

/**
 * EntitySetting.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_entity_setting_delete($params) {
  return civicrm_api3_create_success(CRM_Entitysetting_BAO_EntitySetting::del($params));
}
/**
 * EntitySetting.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_entity_setting_delete_spec(&$spec) {
  $spec['entity_id']['api.required'] = 1;
  $spec['entity_type']['api.required'] = 1;
  $spec['key'] = array(
    'api.required' => TRUE,
    'title' => 'Setting NameSpace - You pass the settings for one key'
  );
  unset($spec['id']);
}
/**
 * EntitySetting.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_entity_setting_get($params) {
  $settings = _civicrm_api3_basic_get('CRM_Entitysetting_BAO_EntitySetting', $params, FALSE);
  $settings = reset($settings);
  $settings = json_decode($settings['setting_data'], TRUE);
  return civicrm_api3_create_success(array($params['entity_id'] => CRM_Utils_Array::value($params['key'], $settings, array())));
}

/**
 * EntitySetting.get API specification
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_entity_setting_get_spec(&$spec) {
  $spec['entity_id']['api.required'] = 1;
  $spec['entity_type']['api.required'] = 1;
  $spec['key'] = array(
    'api.required' => TRUE,
    'title' => 'Setting NameSpace - You pass the settings for one key'
  );
}

/**
 * EntitySetting.getsingle spec
 * (not being added automatically)
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_entity_setting_getsingle_spec(&$spec) {
  _civicrm_api3_entity_setting_get_spec($spec);
}

/**
 * EntitySetting.getsingle spec
 * (not being added automatically)
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_entity_setting_getvalue_spec(&$spec) {
  _civicrm_api3_entity_setting_get_spec($spec);
}

/**
 * EntitySetting.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_entity_setting_getsingle($params) {
  $settings = civicrm_api3_entity_setting_get($params);
  if(empty($settings['values'][$params['entity_id']])) {
    throw new API_Exception("Expected one result found " . count($settings['values']));
  }
  return $settings['values'][$params['entity_id']];
}

/**
 * EntitySetting.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_entity_setting_getvalue($params) {
  $settings = civicrm_api3_entity_setting_getsingle($params);
  if(empty($settings[$params['name']])) {
    throw new API_Exception("Setting {$params['name']} not found");
  }
  return $settings[$params['name']];
}

/**
 * EntitySetting.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_entity_setting_getsettings($params) {
  $settings = CRM_Entitysetting_BAO_EntitySetting::getSettings($params);
  return civicrm_api3_create_success($settings);
}

/**
 * EntitySetting.getSettings API specification
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_entity_setting_getsettings_spec(&$spec) {
  $spec['entity']['api.required'] = 1;
}