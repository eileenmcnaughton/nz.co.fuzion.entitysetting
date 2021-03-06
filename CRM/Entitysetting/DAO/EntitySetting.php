<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2019
 *
 * Generated from /home/dev/civicrm/civicrm-buildkit/build/dmaster/sites/default/files/civicrm/ext/nz.co.fuzion.entitysetting/xml/schema/CRM/Entitysetting/EntitySetting.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:4e2d2ddd8dc3c2a24f6f11f9ff1cd3c2)
 */

/**
 * Database access object for the EntitySetting entity.
 */
class CRM_Entitysetting_DAO_EntitySetting extends CRM_Core_DAO {

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civicrm_entity_setting';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = TRUE;

  /**
   * Unique EntitySetting ID
   *
   * @var int
   */
  public $id;

  /**
   * @var int
   */
  public $entity_id;

  /**
   * Entity Type - camel Case
   *
   * @var string
   */
  public $entity_type;

  /**
   * Json Stored, Extension keyed array of data per entity
   *
   * @var text
   */
  public $setting_data;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civicrm_entity_setting';
    parent::__construct();
  }

  /**
   * Returns all the column names of this table
   *
   * @return array
   */
  public static function &fields() {
    if (!isset(Civi::$statics[__CLASS__]['fields'])) {
      Civi::$statics[__CLASS__]['fields'] = [
        'id' => [
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'description' => CRM_Entitysetting_ExtensionUtil::ts('Unique EntitySetting ID'),
          'required' => TRUE,
          'where' => 'civicrm_entity_setting.id',
          'table_name' => 'civicrm_entity_setting',
          'entity' => 'EntitySetting',
          'bao' => 'CRM_Entitysetting_DAO_EntitySetting',
          'localizable' => 0,
        ],
        'entity_id' => [
          'name' => 'entity_id',
          'type' => CRM_Utils_Type::T_INT,
          'where' => 'civicrm_entity_setting.entity_id',
          'table_name' => 'civicrm_entity_setting',
          'entity' => 'EntitySetting',
          'bao' => 'CRM_Entitysetting_DAO_EntitySetting',
          'localizable' => 0,
        ],
        'entity_type' => [
          'name' => 'entity_type',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => CRM_Entitysetting_ExtensionUtil::ts('Entity Type'),
          'description' => CRM_Entitysetting_ExtensionUtil::ts('Entity Type - camel Case'),
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
          'where' => 'civicrm_entity_setting.entity_type',
          'table_name' => 'civicrm_entity_setting',
          'entity' => 'EntitySetting',
          'bao' => 'CRM_Entitysetting_DAO_EntitySetting',
          'localizable' => 0,
        ],
        'setting_data' => [
          'name' => 'setting_data',
          'type' => CRM_Utils_Type::T_TEXT,
          'title' => CRM_Entitysetting_ExtensionUtil::ts('Setting Data'),
          'description' => CRM_Entitysetting_ExtensionUtil::ts('Json Stored, Extension keyed array of data per entity'),
          'where' => 'civicrm_entity_setting.setting_data',
          'table_name' => 'civicrm_entity_setting',
          'entity' => 'EntitySetting',
          'bao' => 'CRM_Entitysetting_DAO_EntitySetting',
          'localizable' => 0,
        ],
      ];
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'fields_callback', Civi::$statics[__CLASS__]['fields']);
    }
    return Civi::$statics[__CLASS__]['fields'];
  }

  /**
   * Return a mapping from field-name to the corresponding key (as used in fields()).
   *
   * @return array
   *   Array(string $name => string $uniqueName).
   */
  public static function &fieldKeys() {
    if (!isset(Civi::$statics[__CLASS__]['fieldKeys'])) {
      Civi::$statics[__CLASS__]['fieldKeys'] = array_flip(CRM_Utils_Array::collect('name', self::fields()));
    }
    return Civi::$statics[__CLASS__]['fieldKeys'];
  }

  /**
   * Returns the names of this table
   *
   * @return string
   */
  public static function getTableName() {
    return self::$_tableName;
  }

  /**
   * Returns if this table needs to be logged
   *
   * @return bool
   */
  public function getLog() {
    return self::$_log;
  }

  /**
   * Returns the list of fields that can be imported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &import($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'entity_setting', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of fields that can be exported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &export($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'entity_setting', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of indices
   *
   * @param bool $localize
   *
   * @return array
   */
  public static function indices($localize = TRUE) {
    $indices = [];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}
