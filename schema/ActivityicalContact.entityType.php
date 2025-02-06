<?php
use CRM_Activityical_ExtensionUtil as E;

return [
  'name' => 'ActivityicalContact',
  'table' => 'civicrm_activityicalcontact',
  'class' => 'CRM_Activityical_DAO_ActivityicalContact',
  'getInfo' => fn() => [
    'title' => E::ts('Activityical Contact'),
    'title_plural' => E::ts('Activityical Contacts'),
    'description' => E::ts('Per-contact data for activity iCalendar feed'),
    'log' => TRUE,
    'add' => '4.6',
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique ActivityicalContact ID'),
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
    'hash' => [
      'title' => E::ts('Hash'),
      'sql_type' => 'varchar(32)',
      'input_type' => 'Text',
      'description' => E::ts('Private hash per feed'),
      'add' => '4.6',
    ],
  ],
];
