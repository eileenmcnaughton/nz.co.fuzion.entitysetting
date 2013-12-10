<?php

require_once 'entitysetting.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function entitysetting_civicrm_config(&$config) {
  _entitysetting_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function entitysetting_civicrm_xmlMenu(&$files) {
  _entitysetting_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function entitysetting_civicrm_install() {
  return _entitysetting_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function entitysetting_civicrm_uninstall() {
  return _entitysetting_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function entitysetting_civicrm_enable() {
  return _entitysetting_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function entitysetting_civicrm_disable() {
  return _entitysetting_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function entitysetting_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _entitysetting_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function entitysetting_civicrm_managed(&$entities) {
  return _entitysetting_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_buildForm
 *
 * Add entity setting config to admin forms
 */
function entitysetting_civicrm_buildForm($formName, &$form ) {
  if(!_entitysetting_civicrm_is_admin_form_configured($formName)) {
    return;
  }
  $settings = _entitysetting_civicrm_get_form_settings($formName);
  $formSettings = array();
  foreach ($settings as $setting) {
    if(!empty($setting['add_to_setting_form'])) {
      $options = CRM_Entitysetting_BAO_EntitySetting::getOptions($setting);
       foreach ($options as $key => &$value) {
        // specifically 'from_email' has quotes that cause probs
        $value = str_replace(array('"', '<', '>'), ' ', $value);
      }
      $form->addElement($setting['html_type'],
        CRM_Entitysetting_BAO_EntitySetting::getKey($setting),
        ts($setting['title']),
        $options
     );
      $formSettings[] = CRM_Entitysetting_BAO_EntitySetting::getKey($setting);
    }
  }
  $form->assign('entitySettings', $formSettings);
}

/**
 * Implementation of hook_civicrm_alterContent
 *
 * We move the items to the right place here - this is very painful! but it is only on admin forms
 * Think how nice it would be if civi gave us an array!
 * @param unknown $content
 * @param unknown $context
 * @param unknown $tplName
 * @param unknown $object
 */
function entitysetting_civicrm_alterContent(&$content, $context, $tplName, &$object) {
  $formName = get_class($object);
  if(!_entitysetting_civicrm_is_admin_form_configured($formName)) {
    return;
  }
  $settings = _entitysetting_civicrm_get_form_settings($formName);
  $doc = new DOMDocument();
  $doc->loadHTML($content);
  foreach ($settings as $setting) {
    if(!empty($setting['add_to_setting_form'])) {
      $wrapper = $doc->getElementById($setting['form_child_of_id']);
      if($wrapper) {
        $wrapper->appendChild($doc->getElementById('entity-setting-' . CRM_Entitysetting_BAO_EntitySetting::getKey($setting)));
      }
    }
  }
  $content = $doc->saveHTML();
}
/**
 * Implementation of hook_civicrm_buildForm
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function entitysetting_civicrm_postProcess($formName, &$form ) {
  if(!_entitysetting_civicrm_is_admin_form_configured($formName)
    || ($entityID = $form->get('id')) == FALSE) {
    return;
  }
  $submitVars = $form->controller->exportValues($form->get('_name'));
  $settings = _entitysetting_civicrm_get_form_settings($formName);
  foreach ($settings as $setting) {
    //@todo - we aren't handling multiple settings by one extension well here
    civicrm_api3('entity_setting', 'create', array(
      'entity_id' => $entityID,
      'entity_type' => $setting['entity'],
      'settings' => array($setting['name'] => $submitVars[CRM_Entitysetting_BAO_EntitySetting::getKey($setting)]),
      'key' => $setting['key'],
    ));
  }
}

/**
 *
 */
function _entitysetting_civicrm_get_entity_form_mappings() {
  return array(
    'CRM_Admin_Form_ScheduleReminders' => 'action_schedule',
    'CRM_Admin_Page_ScheduleReminders' => 'action_schedule',
  );
}
/**
 *
 * @param unknown $formName
 * @return boolean
 */
function _entitysetting_civicrm_is_admin_form_configured($formName) {
  $adminForms = _entitysetting_civicrm_get_entity_form_mappings();
  return !empty($adminForms[$formName]);
}

/**
 *
 * @param unknown $formName
 * @return boolean
 */
function _entitysetting_civicrm_get_form_settings($formName) {
  $adminForms = _entitysetting_civicrm_get_entity_form_mappings();
  $settings = civicrm_api3('entity_setting', 'getsettings', array('entity' => $adminForms[$formName]));
  return $settings['values'];
}

