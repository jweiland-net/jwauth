<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'fe_users',
    [
        'ip_address' => [
            'exclude' => true,
            'label' => 'LLL:EXT:jwauth/Resources/Private/Language/locallang_db.xlf:fe_users.ip_address',
            'description' => 'LLL:EXT:jwauth/Resources/Private/Language/locallang_db.xlf:fe_users.ip_address.description',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
            ],
        ],
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    'ip_address'
);
