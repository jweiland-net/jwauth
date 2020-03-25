<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

// Check login with each Request
$GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['FE_alwaysFetchUser'] = true;
// Following line allows us to fetch the user data from Session instead of Database.
// But as long as we don't have a real login, we can't deactivate the service directly with help of
// deactivating the extension. The session is still valid.
// That's why I think it's better to do the check with each request
//$GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['FE_fetchUserIfNoSession'] = true;

// add service to get a fe_user with defined IP-Address
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'jwauth',
    'auth',
    \JWeiland\Jwauth\Service\IpAuthService::class,
    [
        'title' => 'FE IP authentication',
        'description' => 'Login to FE with help of IP',
        'subtype' => 'getUserFE,authUserFE',
        'available' => true,
        'priority' => 70,
        // must be higher than \TYPO3\CMS\Sv\AuthenticationService (50) and rsaauth (60) but lower than OpenID (75)
        'quality' => 70,
        'os' => '',
        'exec' => '',
        'className' => \JWeiland\Jwauth\Service\IpAuthService::class
    ]
);

// Delete saved session data from fe_users_session
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['hook_eofe'][] = \JWeiland\Jwauth\FeUser::class . '->clearFeUserSession';
