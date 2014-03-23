<?php

class CRM_Entitysetting_BAO_EntitySetting extends CRM_Entitysetting_DAO_EntitySetting {

  /**
   * Create a new EntitySetting based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Entitysetting_DAO_EntitySetting|NULL
   */
  public static function create($params) {
    $className = 'CRM_Entitysetting_BAO_EntitySetting';
    $entityName = 'EntitySetting';

    $instance = new $className();
    $instance->entity_id = $params['entity_id'];
    $instance->entity_type = $params['entity_type'];
    $instance->find(TRUE);
    $params['setting_data'] = is_null($params['settings']) ? array() : array($params['key'] => $params['settings']);

    if($instance->setting_data) {
      $originalSettingData = json_decode($instance->setting_data, TRUE);
      $untouchedSettings = array_diff_key($originalSettingData, array_merge(array($params['key'] => 1), $params['setting_data']));
      foreach ($params['setting_data'] as $key => $newSettings) {
        if(isset($originalSettingData[$key]) && is_array($originalSettingData[$key])) {
          $params['setting_data'][$key] = array_merge($originalSettingData[$key], $newSettings);
        }
      }
      $params['setting_data'] = $params['setting_data'] + $untouchedSettings;
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

  /**
   * Load up settings metadata from files
   */
  static function loadMetadata($metaDataFolder) {
    $settingMetaData = $entitySettings = array();
    $settingsFiles = CRM_Utils_File::findFiles($metaDataFolder, '*.entity_setting.php');
    foreach ($settingsFiles as $file) {
      $settings = include $file;
      foreach ($settings as $setting) {
        $entitySettings[$setting['entity']][] = $setting;
      }
    }
    foreach ($entitySettings as $entity => $entitySetting) {
      CRM_Core_BAO_Cache::setItem($entitySetting,'CiviCRM setting Spec', $entity);
    }
    return $entitySettings;
  }

  /**
   * This provides information about the enitity settings, allowing setting form generation
   *
   * Function is intended for configuration rather than runtime access to settings
   *
   * @params string $entity e.g contribution_page
   * @params string $key namespace marker - usually the module name
   *
   * @return array $result - the following information as appropriate for each setting
   * - name
   * - type
   * - default
   * - add (CiviCRM version added)
   * - is_domain
   * - is_contact
   * - description
   * - help_text
   */
  static function getSettingSpecification($entity, $key = '', $force = 0) {
    $metadata = CRM_Core_BAO_Cache::getItem('CiviCRM Entity setting Specs', $entity);
    if ($metadata === NULL || $force) {
      $metaDataFolders = $metadata = array();
      self::hookAlterEntitySettingsFolders($metaDataFolders);
      foreach ($metaDataFolders as $metaDataFolder) {
        $metadata = $metadata + self::loadMetaData($metaDataFolder, $entity);
      }
      CRM_Core_BAO_Cache::setItem($metadata, 'CiviCRM Entity setting Specs', $entity);
    }
    return $metadata;
  }

/**
 * get settings for entity
 * @param array $params
 *  - entity = required
 * @return array settings for given entity
 */
  static function getSettings($params) {
    $settings = self::getSettingSpecification($params['entity']);
    return CRM_Utils_Array::value($params['entity'], $settings, array());
  }

  /**
   *
   */
  static function hookAlterEntitySettingsFolders(&$metaDataFolders) {
    $codeVersion = explode('.', CRM_Utils_System::version());
    // if db.ver < code.ver, time to upgrade
    if (version_compare($codeVersion[0] . '.' . $codeVersion[1], 4.5) >= 0) {
      return CRM_Utils_Hook::singleton()->invoke(1, $metaDataFolders,
        self::$_nullObject, self::$_nullObject, self::$_nullObject, self::$_nullObject,
        self::$_nullObject,
        'civicrm_alterEntitySettingsFolders'
      );
    }
    else {
      return CRM_Utils_Hook::singleton()->invoke(1, $metaDataFolders,
        self::$_nullObject, self::$_nullObject, self::$_nullObject, self::$_nullObject,
        'civicrm_alterEntitySettingsFolders'
      );
    }
  }
  /**
   * shortcut to preferably being able to use core pseudoconstant fn
   * @todo - this is copy & paste from the pseudoconstant fn - would prefer to extract & re-use
   */
  static function getoptions($fieldSpec, $params = array(), $context = NULL) {
    $flip = !empty($params['flip']);
    // Merge params with defaults
    $params += array(
      'grouping' => FALSE,
      'localize' => FALSE,
      'onlyActive' => ($context == 'validate' || $context == 'get') ? FALSE : TRUE,
      'fresh' => FALSE,
    );
    if (isset($fieldSpec['enumValues'])) {
      // use of a space after the comma is inconsistent in xml
      $enumStr = str_replace(', ', ',', $fieldSpec['enumValues']);
      $output = explode(',', $enumStr);
      return array_combine($output, $output);
    }

    elseif (!empty($fieldSpec['pseudoconstant'])) {
      $pseudoconstant = $fieldSpec['pseudoconstant'];
      // Merge params with schema defaults
      $params += array(
        'condition' => CRM_Utils_Array::value('condition', $pseudoconstant, array()),
        'keyColumn' => CRM_Utils_Array::value('keyColumn', $pseudoconstant),
        'labelColumn' => CRM_Utils_Array::value('labelColumn', $pseudoconstant),
      );

      // Fetch option group from option_value table
      if(!empty($pseudoconstant['optionGroupName'])) {
        if ($context == 'validate') {
          $params['labelColumn'] = 'name';
        }
        // Call our generic fn for retrieving from the option_value table
        $options = CRM_Core_OptionGroup::values(
          $pseudoconstant['optionGroupName'],
          $flip,
          $params['grouping'],
          $params['localize'],
          $params['condition'] ? ' AND ' . implode(' AND ', (array) $params['condition']) : NULL,
          $params['labelColumn'] ? $params['labelColumn'] : 'label',
          $params['onlyActive'],
          $params['fresh'],
          $params['keyColumn'] ? $params['keyColumn'] : 'value'
        );
         //@todo - this part is not in the core function - allows over-riding of domain-specificity
         // note that only 2 option values are probably affected- from_email_address & grant_types
        if(!empty($fieldSpec['pseudoconstant']['all_domains'])) {
          $allOptions = civicrm_api3('option_value', 'get', array(
            'option_group_name' => $pseudoconstant['optionGroupName'],
            'options' => array('limit' => 500),
          ));
          foreach ($allOptions['values'] as $values) {
            if(empty($options[$values['value']])) {
              $options[$values['value']] = $values['label'];
            }
          }
        }
        return $options;
      }
    }
    elseif(!empty($fieldSpec['options_callback'])) {
      $options = call_user_func_array(array($fieldSpec['options_callback']['class'], $fieldSpec['options_callback']['method']), $fieldSpec['options_callback']['arguments']);
      if(!isset($options['']) && empty($fieldSpec['required'])) {
        $options = array_merge(array('' => '--' . ts('select') . '--'), $options);
      }
      return $options;
    }
  }

  /**
   * We are removing specific html that we know causes problems in the form - identified
   * issue is the 'from_email' field that breaks html with use of < & >
   * @param array $options
   */
  static function sanitiseOptions(&$options) {
    foreach ($options as $key => &$value) {
      // specifically 'from_email' has quotes that cause probs
      $value = str_replace(array('"', '<', '>'), ' ', $value);
    }
  }

  static function getKey($settingSpec) {
    return str_replace('.', '-', $settingSpec['key'] . '__' . $settingSpec['name']);
  }

  static function del($params) {
    $params['settings'] = NULL;
    self::create($params);
  }
}

