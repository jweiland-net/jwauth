<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/jwauth.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Jwauth\Service;

use Doctrine\DBAL\Exception as DBALException;
use TYPO3\CMS\Core\Authentication\AbstractAuthenticationService;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Authenticate FE User against any of its configured IP addresses
 */
class IpAuthService extends AbstractAuthenticationService
{
    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly IpAddressMatcher $ipAddressMatcher,
    ) {}

    /**
     * Get fe_user with an IP address matching the visitor's remote address
     */
    public function getUser(): ?array
    {
        $remoteAddress = htmlspecialchars(strip_tags($this->authInfo['REMOTE_ADDR'] ?? ''));
        if ($remoteAddress === '') {
            // Skip login if remote address does not deliver an IP address.
            return [];
        }

        foreach ($this->fetchFeUsersWithIpAddresses() as $candidate) {
            foreach ($candidate['ipAddresses'] as $ipAddress) {
                if (GeneralUtility::cmpIP($remoteAddress, $ipAddress)) {
                    return $candidate['feUser'];
                }
            }
        }

        return null;
    }

    /**
     * Authenticate user as valid, if any of its IP addresses matches RemoteHost
     */
    public function authUser(array $temporaryUser): int
    {
        $feUserUid = (int)($temporaryUser['uid'] ?? 0);
        $remoteAddress = $this->authInfo['REMOTE_ADDR'] ?? '';

        // this is an additional check against the IP-Address
        // just to be sure
        if ($this->ipAddressMatcher->userHasMatchingIpAddress($feUserUid, $remoteAddress)) {
            // 200 and above indicates a directly authenticated user with no further checks
            return 200;
        }

        // 0 indicates NOT logged in. 100 indicates NOT logged in, but further services can still try to authenticate the user
        return 100;
    }

    /**
     * @return array<int, array{feUser: array<string, mixed>, ipAddresses: list<string>}>
     */
    private function fetchFeUsersWithIpAddresses(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('fe_users');
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));

        try {
            $feUsersWithIpAddress = $queryBuilder
                ->select('fe_users.*', 'ip.ip_address AS matched_ip_address')
                ->from('fe_users')
                ->innerJoin(
                    'fe_users',
                    'tx_jwauth_fe_users_ipaddress_mm',
                    'mm',
                    $queryBuilder->expr()->eq('mm.uid_local', $queryBuilder->quoteIdentifier('fe_users.uid')),
                )
                ->innerJoin(
                    'mm',
                    'tx_jwauth_domain_model_ipaddress',
                    'ip',
                    $queryBuilder->expr()->eq('ip.uid', $queryBuilder->quoteIdentifier('mm.uid_foreign')),
                )
                ->orderBy('fe_users.uid')
                ->executeQuery()
                ->fetchAllAssociative();
        } catch (DBALException | \Exception $exception) {
            return [];
        }

        $candidates = [];
        foreach ($feUsersWithIpAddress as $feUserWithIpAddress) {
            $uid = (int)$feUserWithIpAddress['uid'];
            $ipAddress = $feUserWithIpAddress['matched_ip_address'];
            unset($feUserWithIpAddress['matched_ip_address']);
            $candidates[$uid]['feUser'] ??= $feUserWithIpAddress;
            $candidates[$uid]['ipAddresses'][] = $ipAddress;
        }

        return $candidates;
    }
}
