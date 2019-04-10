<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'fe_users',
    [
        'ip_address' => [
            'exclude' => true,
            'label' => 'IP Address',
            'config' => [
                'type' => 'input',
                'eval' => 'trim'
            ],
        ],
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    'ip_address'
);
