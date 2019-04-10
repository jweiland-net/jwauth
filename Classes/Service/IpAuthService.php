<?php
namespace JWeiland\Jwauth\Service;

/*
 * This file is part of the jwauth project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Service\AbstractService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Authenticate FE User against its IP address
 */
class IpAuthService extends AbstractService
{
    /**
     * @var string
     */
    protected $subType = '';

    /**
     * @var string
     */
    protected $loginData = '';

    /**
     * @var array
     */
    protected $authInfo = '';

    /**
     * @var FrontendUserAuthentication
     */
    protected $feUserAuth;

    /**
     * returns true, if Service is available
     *
     * @return bool
     */
    public function init()
    {
        // @ToDo: Add some checks
        return true;
    }

    /**
     * @param string $subType
     * @param array $loginData
     * @param array $authInfo
     * @param FrontendUserAuthentication $feUserAuth
     */
    public function initAuth($subType, $loginData, $authInfo, \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $feUserAuth)
    {
        $this->subType = $subType;
        $this->loginData = $loginData;
        $this->authInfo = $authInfo;
        $this->feUserAuth = $feUserAuth;
    }

    /**
     * get fe_user with given IP-Address
     *
     * @return array|false
     */
    public function getUser()
    {
        // search on all pids for user records
        $this->authInfo['db_user']['check_pid_clause'] = '';
        // get remote address
        $remoteAddress = htmlspecialchars(strip_tags($this->authInfo['REMOTE_ADDR']));

        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('fe_users');
        $user = $queryBuilder
            ->select('*')
            ->from('fe_users')
            ->where(
                $queryBuilder->expr()->eq(
                    'ip_address',
                    $queryBuilder->createNamedParameter($remoteAddress, \PDO::PARAM_STR)
                )
            )
            ->execute()
            ->fetch();

        if ($user === false) {
            $user = [];
        }

        return $user;
    }

    /**
     * Authenticate user as valid, if IP-Address matches RemoteHost
     *
     * @param array $tempuser
     * @return int
     */
    public function authUser(array $tempuser)
    {
        // this is an additional check against the IP-Address
        // just to be sure
        if (GeneralUtility::cmpIP($this->authInfo['REMOTE_ADDR'], $tempuser['ip_address'])) {
            // 200 and above indicates a directly authenticated user with no further checks
            return 200;
        } else {
            // 0 indicates NOT logged in. 100 indicates NOT logged in, but further services can still try to authenticate the user
            return 100;
        }
    }

    /**
     * Get TYPO3s Connection Pool
     *
     * @return ConnectionPool
     */
    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
