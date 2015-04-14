<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$tmp_feusers_columns = array(
	'ip_address' => array(
		'exclude' => 1,
		'label' => 'IP Address',
		'config' => array (
			'type' => 'input',
			'eval' => 'trim'
		),
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tmp_feusers_columns);
$GLOBALS['TCA']['fe_users']['types']['0']['showitem'] .= ',ip_address';