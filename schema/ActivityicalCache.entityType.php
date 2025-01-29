<?php
use CRM_Activityical_ExtensionUtil as E;

return [
  'name' => 'ActivityicalCache',
  'table' => 'civicrm_activityicalcache',
  'class' => 'CRM_Activityical_DAO_ActivityicalCache',
  'getInfo' => fn() => [
    'title' => E::ts('Activityical Cache'),
    'title_plural' => E::ts('Activityical Caches'),
    'description' => E::ts('Cached activity iCalendar feed contents, per contact'),
    'log' => FALSE,
    'add' => '4.6',
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique ActivityicalCache ID'),
      'add' => '4.6',
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'contact_id' => [
      'title' => E::ts('Contact ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to Contact'),
      'add' => '4.6',
      'entity_reference' => [
        'entity' => 'Contact',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'cache' => [
      'title' => E::ts('Cache'),
      'sql_type' => 'longtext',
      'input_type' => 'TextArea',
      'description' => E::ts('Cached feed output'),
      'add' => '4.6',
    ],
    'cached' => [
      'title' => E::ts('Cached'),
      'sql_type' => 'timestamp',
      'input_type' => NULL,
      'description' => E::ts('Timestamp'),
      'add' => '4.6',
    ],
  ],
];
