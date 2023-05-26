<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/jwauth.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Jwauth\Service;

use Doctrine\DBAL\DBALException;
use JWeiland\Jwauth\Traits\ConnectionPoolTrait;
use TYPO3\CMS\Core\Authentication\AbstractAuthenticationService;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Authenticate FE User against its IP address
 */
class IpAuthService extends AbstractAuthenticationService
{
    use ConnectionPoolTrait;

    /**
     * Get fe_user with given IP-Address
     */
    public function getUser(): ?array
    {
        $remoteAddress = htmlspecialchars(strip_tags($this->authInfo['REMOTE_ADDR'] ?? ''));
        if ($remoteAddress === '') {
            // Skip login if remote address does not deliver an IP address.
            return [];
        }

        $bestMatchingUser = $this->getBestMatchingUser($remoteAddress);
        if ($bestMatchingUser === []) {
            $bestMatchingUser = $this->getBestPartlyMatchingUser($remoteAddress);
        }

        return $bestMatchingUser ?: null;
    }

    private function getBestMatchingUser(string $remoteAddress): array
    {
        $queryBuilder = $this->getPreparedQueryBuilderForFeUsers();
        try {
            $frontendUser = $queryBuilder
                ->where(
                    $queryBuilder->expr()->eq(
                        'ip_address',
                        $queryBuilder->createNamedParameter($remoteAddress)
                    )
                )
                ->executeQuery()
                ->fetchAssociative();

            if ($frontendUser === false) {
                $frontendUser = [];
            }
        } catch (DBALException | \Exception $exception) {
            $frontendUser = [];
        }


        return $frontendUser;
    }

    private function getBestPartlyMatchingUser(string $remoteAddress): array
    {
        $isIPv6 = (bool)strpos($remoteAddress, ':');
        $divider = $isIPv6 ? ':' : '.';

        $matchedFrontendUsers = [];
        $addressParts = GeneralUtility::trimExplode($divider, $remoteAddress);
        array_pop($addressParts);
        $queryBuilder = $this->getPreparedQueryBuilderForFeUsers();
        while ($addressParts) {
            try {
                $frontendUsers = $queryBuilder
                    ->where(
                        $queryBuilder->expr()->neq(
                            'ip_address',
                            $queryBuilder->createNamedParameter('')
                        ),
                        $queryBuilder->expr()->like(
                            'ip_address',
                            $queryBuilder->createNamedParameter(implode($divider, $addressParts) . '%')
                        )
                    )
                    ->executeQuery()
                    ->fetchAllAssociative();
            } catch (DBALException | \Exception $exception) {
                $frontendUsers = [];
            }

            $matchedFrontendUsers = array_filter($frontendUsers, static function ($frontendUser): bool {
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

    /**
     * Authenticate user as valid, if IP-Address matches RemoteHost
     */
    public function authUser(array $temporaryUser): int
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

    private function getPreparedQueryBuilderForFeUsers(): QueryBuilder
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('fe_users');
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));

        return $queryBuilder
            ->select('*')
            ->from('fe_users');
    }
}
