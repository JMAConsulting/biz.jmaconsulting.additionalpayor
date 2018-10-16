<?php

require_once 'additionalpayor.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function additionalpayor_civicrm_config(&$config) {
  _additionalpayor_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function additionalpayor_civicrm_xmlMenu(&$files) {
  _additionalpayor_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function additionalpayor_civicrm_install() {
  _additionalpayor_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function additionalpayor_civicrm_postInstall() {
  _additionalpayor_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function additionalpayor_civicrm_uninstall() {
  _additionalpayor_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function additionalpayor_civicrm_enable() {
  _additionalpayor_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function additionalpayor_civicrm_disable() {
  _additionalpayor_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function additionalpayor_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _additionalpayor_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function additionalpayor_civicrm_managed(&$entities) {
  _additionalpayor_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function additionalpayor_civicrm_caseTypes(&$caseTypes) {
  _additionalpayor_civix_civicrm_caseTypes($caseTypes);
}

function additionalpayor_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Financial_Form_PaymentEdit') {
    if ($id = $form->getVar('_id')) {
      $form->assign('customDataType', 'FinancialTrxn');
      $form->assign('entityID', $id);
    }
    CRM_Core_Region::instance('payment-edit-block')->add(array(
      'template' => __DIR__ . '/templates/FinancialTrxnCustom.tpl',
    ));
  }
  if ('CRM_Findpayment_Form_Search' == $formName) {
    CRM_Core_BAO_Query::addCustomFormFields($form, array('FinancialTrxn'));
  }
}

function additionalpayor_civicrm_searchColumns($contextName, &$columnHeaders, &$rows, $form) {
  if ($contextName == 'findpayment') {
    $tableName = civicrm_api3('CustomGroup', 'getvalue', array(
      'name' => 'additional_payor',
      'return' => 'table_name',
    ));
    $customField = civicrm_api3('customField', 'getsingle', array(
      'custom_group_id' => 'additional_payor',
      'name' => 'additional_payor',
    ));
    $fieldName = 'custom_' . $customField['id'];
    foreach ($rows as $key => &$row) {
      if (!in_array($fieldName, array_keys($row))) {
        $row[$fieldName] = CRM_Core_DAO::singleValueQuery(sprintf("SELECT %s FROM %s WHERE entity_id = %d ", $customField['column_name'], $tableName, $row['financialtrxn_id']));
      }
      if (!empty($row[$fieldName])) {
        $row['sort_name'] = civicrm_api3('Contact', 'getvalue', ['id' => $row[$fieldName], 'return' => 'sort_name']);
        $row['contact_id'] = $row[$fieldName];
      }
    }
  }
}

function additionalpayor_civicrm_preProcess($formName, &$form) {
  if ($formName == 'CRM_Financial_Form_PaymentEdit') {
    $form->assign('customDataType', 'FinancialTrxn');
    $id = $form->getVar('_id');
    if ($id) {
      $form->assign('entityID', $id);
    }
    if (!empty($_POST['hidden_custom'])) {
      $form->set('type', 'FinancialTrxn');
      CRM_Custom_Form_CustomData::preProcess($form, NULL, NULL, 1, 'FinancialTrxn', $id);
      CRM_Custom_Form_CustomData::buildQuickForm($form);
      CRM_Custom_Form_CustomData::setDefaultValues($form);
    }
  }
}

function additionalpayor_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_Financial_Form_PaymentEdit' && ($id = $form->getVar('_id'))) {
    $customValues = CRM_Core_BAO_CustomField::postProcess($form->_submitValues, $id, 'FinancialTrxn');
    if (!empty($customValues) && is_array($customValues)) {
      CRM_Core_BAO_CustomValueTable::store($customValues, 'civicrm_financial_trxn', $id);
    }
  }
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function additionalpayor_civicrm_angularModules(&$angularModules) {
  _additionalpayor_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function additionalpayor_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _additionalpayor_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function additionalpayor_civicrm_entityTypes(&$entityTypes) {
  _additionalpayor_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function additionalpayor_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function additionalpayor_civicrm_navigationMenu(&$menu) {
  _additionalpayor_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _additionalpayor_civix_navigationMenu($menu);
} // */
