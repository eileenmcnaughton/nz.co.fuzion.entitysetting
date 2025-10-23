<?php
use CRM_Entitysetting_ExtensionUtil as E;

return [
  'name' => 'EntitySetting',
  'table' => 'civicrm_entity_setting',
  'class' => 'CRM_Entitysetting_DAO_EntitySetting',
  'getInfo' => fn() => [
    'title' => E::ts('Entity Setting'),
    'title_plural' => E::ts('Entity Settings'),
    'description' => E::ts('Table containing entity settings'),
    'log' => TRUE,
    'add' => '4.4',
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique EntitySetting ID'),
      'add' => '4.4',
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'entity_id' => [
      'title' => E::ts('Entity ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'add' => '4.4',
    ],
    'entity_type' => [
      'title' => E::ts('Entity Type'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => E::ts('Entity Type - camel Case'),
      'add' => '4.4',
    ],
    'setting_data' => [
      'title' => E::ts('Setting Data'),
      'sql_type' => 'text',
      'input_type' => 'TextArea',
      'description' => E::ts('Json Stored, Extension keyed array of data per entity'),
      'serialize' => CRM_Core_DAO::SERIALIZE_JSON,
      'add' => '4.4',
    ],
  ],
];
