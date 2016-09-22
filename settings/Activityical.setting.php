<?php

return array(
  'activityical_group_id' => array(
    'group_name' => 'Activityical Settings',
    'group' => 'activityical',
    'name' => 'activityical_group_id',
    'type' => 'Int',
    'add' => '4.6',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Only menbers of this CiviCRM group will have an activity iCalendar feed.',
    'title' =>  'Activity iCalendar Feed Group',
    'help_text' => '',
    'html_type' => 'Select',
    'html_attributes' => array(
      'size' => 10,
      'class' => 'crm-form-multiselect',
    ),
    'quick_form_type' => 'Element',
    'X_options_callback' => 'CRM_Activityical_Form_Settings::getGroupOptions'
  ),
 );