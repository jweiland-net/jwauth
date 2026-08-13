<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/jwauth.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

return [
    'ctrl' => [
        'title' => 'LLL:EXT:jwauth/Resources/Private/Language/locallang_db.xlf:tx_jwauth_domain_model_ipaddress',
        'label' => 'ip_address',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'adminOnly' => true,
        'rootLevel' => 1,
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'ip_address',
    ],
    'types' => [
        '1' => [
            'showitem' => 'ip_address, hidden',
        ],
    ],
    'columns' => [
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        'label' => '',
                        'value' => '',
                        'invertStateDisplay' => true,
                    ],
                ],
            ],
        ],
        'ip_address' => [
            'exclude' => true,
            'label' => 'LLL:EXT:jwauth/Resources/Private/Language/locallang_db.xlf:tx_jwauth_domain_model_ipaddress.ip_address',
            'description' => 'LLL:EXT:jwauth/Resources/Private/Language/locallang_db.xlf:tx_jwauth_domain_model_ipaddress.ip_address.description',
            'config' => [
                'type' => 'input',
                'max' => 43,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
    ],
];
