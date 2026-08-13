<?php

/*
 * This file is part of the package jweiland/jwauth.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use JWeiland\Jwauth\Form\FieldWizard\RemoteAddress;
use JWeiland\Jwauth\Service\IpAuthService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

// Check login with each Request
$GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['FE_alwaysFetchUser'] = true;

// Register a fieldWizard to show the REMOTE_ADDR TYPO3 currently detects below
// the ip_addresses (fe_users) and ip_address (tx_jwauth_domain_model_ipaddress) fields.
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1755000000] = [
    'nodeName' => 'remoteAddress',
    'priority' => 40,
    'class' => RemoteAddress::class,
];

// Following line allows us to fetch the user data from Session instead of Database.
// But as long as we don't have a real login, we can't deactivate the service directly with help of
// deactivating the extension. The session is still valid.
// That's why I think it's better to do the check with each request
// $GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['FE_fetchUserIfNoSession'] = true;

// Add service to get a fe_user with defined IP-Address
ExtensionManagementUtility::addService(
    'jwauth',
    'auth',
    IpAuthService::class,
    [
        'title' => 'FE IP authentication',
        'description' => 'Login to FE with help of IP',
        'subtype' => 'getUserFE,authUserFE',
        'available' => true,
        'priority' => 70,
        // Must be higher than \TYPO3\CMS\Sv\AuthenticationService (50) and rsaauth (60) but lower than OpenID (75)
        'quality' => 70,
        'os' => '',
        'exec' => '',
        'className' => IpAuthService::class,
    ],
);
