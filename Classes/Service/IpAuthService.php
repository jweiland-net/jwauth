<?php

declare(strict_types = 1);

/*
 * This file is part of the package jweiland/jwauth.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Jwauth\Service;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
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
    protected $authInfo = [];

    /**
     * @var FrontendUserAuthentication
     */
    protected $feUserAuth;

    /**
     * Returns true, if Service is available
     */
    public function init(): bool
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
    public function initAuth(
        string $subType,
        array $loginData,
        array $authInfo,
        FrontendUserAuthentication $feUserAuth
    ): void {
        $this->subType = $subType;
        $this->loginData = $loginData;
        $this->authInfo = $authInfo;
        $this->feUserAuth = $feUserAuth;
    }

    /**
     * Get fe_user with given IP-Address
     *
     * @return array Empty array, if user was not found
     */
    public function getUser(): array
    {
        $remoteAddress = htmlspecialchars(strip_tags($this->authInfo['REMOTE_ADDR'] ?? ''));
        if (empty($remoteAddress)) {
            // Do not try to fetch one of all fe_users where no IP address is assigned
            return [];
        }

        $bestMatchingUser = $this->getBestMatchingUser($remoteAddress);
        if (empty($bestMatchingUser)) {
            $bestMatchingUser = $this->getBestPartlyMatchingUser($remoteAddress);
        }
        return $bestMatchingUser;
    }

    public function getBestMatchingUser(string $remoteAddress): array
    {
        $queryBuilder = $this->getPreparedQueryBuilderForFeUsers();
        $frontendUser = $queryBuilder
            ->where(
                $queryBuilder->expr()->eq(
                    'ip_address',
                    $queryBuilder->createNamedParameter($remoteAddress, \PDO::PARAM_STR)
                )
            )
            ->execute()
            ->fetch();

        if ($frontendUser === false) {
            $frontendUser = [];
        }

        return $frontendUser;
    }

    public function getBestPartlyMatchingUser(string $remoteAddress): array
    {
        $isIPv6 = (bool)strpos($remoteAddress, ':');
        $divider = $isIPv6 ? ':' : '.';
        $matchedFrontendUsers = [];
        $addressParts = GeneralUtility::trimExplode($divider, $remoteAddress);
        array_pop($addressParts);
        $queryBuilder = $this->getPreparedQueryBuilderForFeUsers();
        while ($addressParts) {
            $frontendUsers = $queryBuilder
                ->where(
                    $queryBuilder->expr()->neq(
                        'ip_address',
                        $queryBuilder->createNamedParameter('', \PDO::PARAM_STR)
                    ),
                    $queryBuilder->expr()->like(
                        'ip_address',
                        $queryBuilder->createNamedParameter(
                            implode($divider, $addressParts) . '%',
                            \PDO::PARAM_STR)
                    )
                )
                ->execute()
                ->fetchAll();

            $matchedFrontendUsers = array_filter($frontendUsers, function($frontendUser) {
                return GeneralUtility::cmpIP($this->authInfo['REMOTE_ADDR'], $frontendUser['ip_address']);
            });
            if (!empty($matchedFrontendUsers)) {
                break;
            }
            array_pop($addressParts);
        }

        $matchedFrontendUser = array_shift($matchedFrontendUsers);
        return $matchedFrontendUser ?? [];
    }

    protected function getPreparedQueryBuilderForFeUsers(): QueryBuilder
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('fe_users');
        return $queryBuilder
            ->select('*')
            ->from('fe_users');
    }

    /**
     * Authenticate user as valid, if IP-Address matches RemoteHost
     *
     * @param array $temporaryUser
     * @return int
     */
    public function authUser(array $temporaryUser)
    {
        // this is an additional check against the IP-Address
        // just to be sure
        if (GeneralUtility::cmpIP($this->authInfo['REMOTE_ADDR'], $temporaryUser['ip_address'])) {
            // 200 and above indicates a directly authenticated user with no further checks
            return 200;
        }

        // 0 indicates NOT logged in. 100 indicates NOT logged in, but further services can still try to authenticate the user
        return 100;
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
