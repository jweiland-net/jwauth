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
        'versioningWS' => true,
        'origUid' => 't3_origuid',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'adminOnly' => true,
        'rootLevel' => 1,
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => 'ip_address',
        'typeicon_classes' => [
            'default' => 'jwauth-ipaddress',
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => '
                --palette--;;languageHidden, ip_address,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.access,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.access;access',
        ],
    ],
    'palettes' => [
        'languageHidden' => ['showitem' => 'sys_language_uid, l10n_parent, hidden'],
        'access' => [
            'showitem' => 'starttime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel,endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel',
        ],
    ],
    'columns' => [
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
