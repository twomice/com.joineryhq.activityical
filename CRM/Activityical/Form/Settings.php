<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class for extension Settings form.
 * Borrowed heavily from
 * https://github.com/eileenmcnaughton/nz.co.fuzion.civixero/blob/master/CRM/Civixero/Form/XeroSettings.php
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Activityical_Form_Settings extends CRM_Core_Form {

  private $_settingFilter = array('group' => 'activityical');
  private $_submittedValues = array();
  private $_settings = array();

  function buildQuickForm() {
    $settings = $this->getFormSettings();
    foreach ($settings as $name => $setting) {
      if (isset($setting['quick_form_type'])) {
        switch($setting['html_type']) {
          case 'Select':
            $this->add(
              $setting['html_type'], // field type
              $setting['name'], // field name
              $setting['title'], // field label
              $this->getSettingOptions($setting),
              NULL,
              $setting['html_attributes']
            );
            break;
          case 'CheckBox':
            $this->addCheckBox(
              $setting['name'], // field name
              $setting['title'], // field label
              array_flip($this->getSettingOptions($setting))
            );
            break;
          case 'Radio':
            $this->addRadio(
              $setting['name'], // field name
              $setting['title'], // field label
              $this->getSettingOptions($setting)
            );
            break;
          default:
            $add = 'add' . $setting['quick_form_type'];
            if ($add == 'addElement') {
              $this->$add($setting['html_type'], $name, ts($setting['title']), CRM_Utils_Array::value('html_attributes', $setting, array()));
            }
            else {
              $this->$add($name, ts($setting['title']));
            }
            break;
        }
      }
      $descriptions[$setting['name']] = ts($setting['description']);

      if (!empty($setting['X_form_rules_args'])) {
        $rules_args = (array)$setting['X_form_rules_args'];
        foreach ($rules_args as $rule_args) {
          array_unshift($rule_args, $setting['name']);
          call_user_func_array(array($this, 'addRule'), $rule_args);
        }
      }
    }
    $this->assign("descriptions", $descriptions);

    $this->addButtons(array(
      array (
        'type' => 'submit',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      )
    ));

    CRM_Core_Resources::singleton()->addStyleFile('com.joineryhq.activityical', 'css/activityical.css');

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  function postProcess() {
    $this->_submittedValues = $this->exportValues();
    $this->saveSettings();
    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/admin/activityical/settings', 'reset=1'));
    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons". These
    // items don't have labels. We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

  /**
   * Get the settings we are going to allow to be set on this form.
   *
   * @return array
   */
  function getFormSettings() {
    if (empty($this->_settings)) {
      $settings = civicrm_api3('setting', 'getfields', array('filters' => $this->_settingFilter));
    }
    $settings = $settings['values'];
    return $settings;
  }

  /**
   * Get the settings we are going to allow to be set on this form.
   *
   * @return array
   */
  function saveSettings() {
    $settings = $this->getFormSettings();
    $values = array_intersect_key($this->_submittedValues, $settings);
    civicrm_api3('setting', 'create', $values);

    // Save any that are not submitted, as well (e.g., checkboxes that aren't checked).
    $unsettings = array_fill_keys(array_keys(array_diff_key($settings, $this->_submittedValues)), NULL);
    civicrm_api3('setting', 'create', $unsettings);

    CRM_Core_Session::setStatus(" ", ts('Settings saved.'), "success");

    // Clear cache, on the assumption that any change in this form will change
    // the feed output.
    CRM_Activityical_Cache::clearAll();
  }

  /**
   * Set defaults for form.
   *
   * @see CRM_Core_Form::setDefaultValues()
   */
  function setDefaultValues() {
    $result = civicrm_api3('setting', 'get', array('return' => array_keys($this->getFormSettings())));
    $domainID = CRM_Core_Config::domainID();
    $ret = CRM_Utils_Array::value($domainID, $result['values']);
    return $ret;
  }

  public static function getGroupOptions() {
    $result = civicrm_api3('Group', 'get', array(
      'is_active' => 1,
    ));
    foreach ($result['values'] as $id => $value) {
      $options[$id] = $value['title'];
    }
    asort($options);
    $options = array(0 => '- '. ts('none') . ' -') + $options;
    return $options;
  }

  public static function getActivityTypeOptions() {
    $options = array();
    $result = civicrm_api3('OptionValue', 'get', array(
      'option_group_id' => "activity_type",
      'is_active' => 1,
    ));
    foreach ($result['values'] as $id => $value) {
      $options[$value['value']] = $value['label'];
    }
    asort($options);
    return $options;
  }

  public function getSettingOptions($setting) {
    if (!empty($setting['X_options_callback']) && is_callable($setting['X_options_callback'])) {
      return call_user_func($setting['X_options_callback']);
    }
    else {
      return CRM_Utils_Array::value('X_options', $setting);
    }
  }
}

