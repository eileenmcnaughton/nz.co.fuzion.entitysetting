<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once 'entitysetting.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function entitysetting_civicrm_config(&$config) {
  _entitysetting_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_install
 */
function entitysetting_civicrm_install() {
  _entitysetting_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_enable
 */
function entitysetting_civicrm_enable() {
  _entitysetting_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_buildForm
 *
 * Add entity setting config to admin forms
 * @param string $formName name of form
 * @param object $form form object
 */
function entitysetting_civicrm_buildForm($formName, &$form ) {
  if(!_entitysetting_civicrm_is_admin_form_configured($formName)) {
    return;
  }
  $settings = _entitysetting_civicrm_get_form_settings($formName);

  foreach ($settings as $formKey => $setting) {
    $options = CRM_Entitysetting_BAO_EntitySetting::getOptions($setting);
    if($options) {
      CRM_Entitysetting_BAO_EntitySetting::sanitiseOptions($options);
    }

    if ($setting['html_type'] == 'Radio') {
      $form->addRadio($formKey, ts($setting['title']), $options, ['allowClear' => TRUE]);
    }
    else {
      $form->addElement($setting['html_type'],
        $formKey,
        ts($setting['title']),
        $options
      );
    }
    if(($entity_id = $form->get('id')) != FALSE) {
      _entity_civicrm_set_form_defaults($form, $setting, $entity_id, $formKey);
    }
  }
  $form->assign('entitySettings', $settings);
}

/**
 * Implementation of hook_civicrm_alterContent
 *
 * We move the items to the right place here - this is very painful! but it is only on admin forms
 * Think how nice it would be if civi gave us an array!
 */
function entitysetting_civicrm_alterContent(&$content, $context, $tplName, &$object) {
  $formName = get_class($object);
  if (!in_array($object->getVar('_action'), [CRM_Core_Action::ADD, CRM_Core_Action::UPDATE])) {
    return;
  }
  if(!_entitysetting_civicrm_is_admin_form_configured($formName)) {
    return;
  }
  $settings = _entitysetting_civicrm_get_form_settings($formName);
  if(empty($settings)) {
    return;
  }
  libxml_use_internal_errors(true);
  $doc = new IvoPetkov\HTML5DOMDocument();
  $doc->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), IvoPetkov\HTML5DOMDocument::ALLOW_DUPLICATE_IDS);
  libxml_clear_errors();
  // note that forms are inconsistent as to which items have ids so we have append to,
  // insert before & even insert before before
  //@todo - we need to rationalise this - but first figuring out the various possibilities
  // would be better it all table rows had an id - & all tables - but not sure if that is right approach
  foreach ($settings as $setting) {
    if(!empty($setting['add_to_setting_form'])) {
      if(!empty($setting['form_child_of_id'])) {
        $wrapper = $doc->getElementById($setting['form_child_of_id']);
      }
      elseif (!empty($setting['form_child_of_parent'])) {
        //check it exists to avoid warning
        if($doc->getElementById($setting['form_child_of_parent'])) {
          $wrapper = $doc->getElementById($setting['form_child_of_parent'])->parentNode;
        }
      }
      elseif (!empty($setting['form_child_of_parents_parent'])) {
        if($doc->getElementById($setting['form_child_of_parents_parent'])) {
          $wrapper = $doc->getElementById($setting['form_child_of_parents_parent'])->parentNode->parentNode;
        }
      }
      elseif (!empty($setting['form_child_of_parents_parents_parent'])) {
        if($doc->getElementById($setting['form_child_of_parents_parents_parent'])) {
          $wrapper = $doc->getElementById($setting['form_child_of_parents_parents_parent'])->parentNode->parentNode->parentNode;
        }
      }
      if(!empty($wrapper)) { // we need this if for submit
        $wrapper->appendChild($doc->getElementById('entity-setting-' . CRM_Entitysetting_BAO_EntitySetting::getKey($setting)));
      }
    }
  }
  $content = '<div id="crm-container" class="crm-container">';
  $content .= $doc->saveHTML();
  $content .= '</div>';
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
    $settingKey = CRM_Entitysetting_BAO_EntitySetting::getKey($setting);
    $settingValue = isset($submitVars[$settingKey]) ? $submitVars[$settingKey] : NULL;
    //@todo - we aren't handling multiple settings by one extension well here as we are
    // setting them each individually rather than combining into one array first
    civicrm_api3('entity_setting', 'create', [
      'entity_id' => $entityID,
      'entity_type' => $setting['entity'],
      'settings' => [$setting['name'] => $settingValue],
      'key' => $setting['key'],
    ]);
  }
}

/**
 * implements pageRun hook
 * Assign form settings to help page
 * @param unknown $page
 */
function entitysetting_civicrm_pageRun(&$page) {
  $pageName = get_class($page);
  if($pageName != 'CRM_Core_Page_Inline_Help') {
    return;
  }
  $pageClass = str_replace('/', '_', $_REQUEST['file']);
  if(!_entitysetting_civicrm_is_admin_form_configured($pageClass)) {
    return;
  }
  $settings = _entitysetting_civicrm_get_form_settings($pageClass);
  CRM_Core_Smarty::singleton()->assign('entitySettings', $settings);
}

/**
 *
 */
function _entitysetting_civicrm_get_entity_form_mappings() {
  return [
    'CRM_Admin_Form_ScheduleReminders' => 'action_schedule',
    'CRM_Admin_Page_ScheduleReminders' => 'action_schedule',
    'CRM_Admin_Form_RelationshipType' => 'relationship_type',
    'CRM_Admin_Page_RelationshipType' => 'relationship_type',
    'CRM_Contribute_Form_ContributionPage_Settings' => 'contribution_page',
    'CRM_Event_Form_ManageEvent_EventInfo' => 'event',
    'CRM_Event_Form_ManageEvent_Registration' => 'event',
    'CRM_Event_Form_ManageEvent_Fee' => 'event_fee',

  ];
}

/**
 * Assign relevant setting values to form
 * @param \CRM_Core_Form $form
 */
function _entitysetting_assign_form_settings(&$form) {

}

/**
 * @param string $formName
 * @return boolean
 */
function _entitysetting_civicrm_is_admin_form_configured($formName) {
  $adminForms = _entitysetting_civicrm_get_entity_form_mappings();
  return !empty($adminForms[$formName]);
}

/**
 * Get array of settings to be added to the form
 *
 * @param string $formName Name of form
 *
 * @return array
 * @throws \CRM_Core_Exception
 */
function _entitysetting_civicrm_get_form_settings($formName) {
  $adminForms = _entitysetting_civicrm_get_entity_form_mappings();
  if(empty($adminForms[$formName])) {
    return [];
  }
  $settings = civicrm_api3('entity_setting', 'getsettings', ['entity' => $adminForms[$formName]]);
  if(empty($settings['values'])) {
    return [];
  }
  $formSettings = [];
  foreach ($settings['values'] as $key => $setting) {
    $formKey = CRM_Entitysetting_BAO_EntitySetting::getKey($setting);
    if(!empty($setting['add_to_setting_form'])) {
      $formSettings[$formKey] = $setting;
    }
  }
  return $formSettings;
}

function _entity_civicrm_set_form_defaults(&$form, $setting, $entity_id, $formKey) {
  try{
    $default = civicrm_api3('entity_setting', 'getvalue', [
      'key' => $setting['key'],
      'name' => $setting['name'],
      'entity_type' => $setting['entity'],
      'entity_id' => $entity_id,
    ]);
    $form->setDefaults([$formKey => $default]);
  }
  catch(Exception $e) {
    // don't set the default
  }
}

/**
 * Implements hook_civicrm_entityTypes.
 *
 * @param array $entityTypes
 *   Registered entity types.
 */
function entitysetting_civicrm_entityTypes(&$entityTypes) {
  $entityTypes['CRM_Entitysetting_DAO_EntitySetting'] = [
    'name' => 'EntitySetting',
    'class' => 'CRM_Entitysetting_DAO_EntitySetting',
    'table' => 'civicrm_entity_setting',
  ];
}
