<?php

/*
 * This file is part of the package jweiland/jwauth.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

if (!defined('TYPO3')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'fe_users',
    [
        'ip_addresses' => [
            'exclude' => true,
            'label' => 'LLL:EXT:jwauth/Resources/Private/Language/locallang_db.xlf:fe_users.ip_addresses',
            'description' => 'LLL:EXT:jwauth/Resources/Private/Language/locallang_db.xlf:fe_users.ip_addresses.description',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_jwauth_domain_model_ipaddress',
                'foreign_table_where' => ' ORDER BY tx_jwauth_domain_model_ipaddress.ip_address ASC',
                'MM' => 'tx_jwauth_fe_users_ipaddress_mm',
                'size' => 5,
                'minitems' => 0,
                'default' => 0,
                'fieldControl' => [
                    'addRecord' => [
                        'disabled' => false,
                        'options' => [
                            'setValue' => 'append',
                        ],
                    ],
                ],
            ],
        ],
    ],
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    'ip_addresses',
);
