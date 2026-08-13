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
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Checks whether a fe_user has any IP address pattern that matches a given remote address.
 * Used by IpAuthService::authUser() and by ClearIpAuthenticatedSessionMiddleware, so both
 * places share the same matching logic against the tx_jwauth_domain_model_ipaddress relation.
 */
class IpAddressMatcher
{
    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {}

    public function userHasMatchingIpAddress(int $feUserUid, string $remoteAddress): bool
    {
        if ($feUserUid <= 0 || $remoteAddress === '') {
            return false;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_jwauth_domain_model_ipaddress');
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));

        try {
            $ipAddresses = $queryBuilder
                ->select('ip.ip_address')
                ->from('tx_jwauth_domain_model_ipaddress', 'ip')
                ->innerJoin(
                    'ip',
                    'tx_jwauth_fe_users_ipaddress_mm',
                    'mm',
                    $queryBuilder->expr()->eq('mm.uid_foreign', $queryBuilder->quoteIdentifier('ip.uid')),
                )
                ->where(
                    $queryBuilder->expr()->eq(
                        'mm.uid_local',
                        $queryBuilder->createNamedParameter($feUserUid, Connection::PARAM_INT),
                    ),
                )
                ->executeQuery()
                ->fetchFirstColumn();
        } catch (DBALException | \Exception $exception) {
            return false;
        }

        foreach ($ipAddresses as $ipAddress) {
            if (GeneralUtility::cmpIP($remoteAddress, $ipAddress)) {
                return true;
            }
        }

        return false;
    }
}
