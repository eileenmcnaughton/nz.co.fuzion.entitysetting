<?php

class CRM_Entitysetting_BAO_EntitySetting extends CRM_Entitysetting_DAO_EntitySetting {

  /**
   * Create a new EntitySetting based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Entitysetting_DAO_EntitySetting|NULL
   */
  public static function create($params) {
    $className = 'CRM_Entitysetting_DAO_EntitySetting';
    $entityName = 'EntitySetting';

    $instance = new $className();
    $instance->entity_id = $params['entity_id'];
    $instance->entity_type = $params['entity_type'];
    $instance->find(TRUE);
    $params['setting_data'] = array($params['key'] => $params['settings']);

    if($instance->setting_data) {
      $params['setting_data'] = array_merge(json_decode($instance->setting_data, TRUE), $params['setting_data']);
    }
    if(is_array($params['setting_data'])) {
      $params['setting_data'] = json_encode($params['setting_data']);
    }
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }
}
