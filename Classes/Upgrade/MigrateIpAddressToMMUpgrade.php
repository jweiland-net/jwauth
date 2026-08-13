<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/jwauth.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Jwauth\Upgrade;

use Doctrine\DBAL\Exception as DBALException;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Migrates the legacy single-value fe_users.ip_address column into the new
 * tx_jwauth_domain_model_ipaddress table and its MM relation.
 */
#[UpgradeWizard('jwauth_migrateIpAddress')]
final class MigrateIpAddressToMMUpgrade implements ChattyInterface, RepeatableInterface, UpgradeWizardInterface
{
    private ?OutputInterface $output = null;

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {}

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function getTitle(): string
    {
        return '[jwauth] Migrate ip_address into new mm-table';
    }

    public function getDescription(): string
    {
        return 'Migrate the legacy fe_users.ip_address value into the new tx_jwauth_domain_model_ipaddress relation';
    }

    public function updateNecessary(): bool
    {
        $queryBuilder = $this->getUnmigratedRecordsQueryBuilder();

        try {
            $schemaManager = $queryBuilder->getConnection()->createSchemaManager();
        } catch (DBALException | \Exception $exception) {
            return false;
        }

        if (!array_key_exists('ip_address', $schemaManager->listTableColumns('fe_users'))) {
            // Legacy column already gone, e.g. a fresh install without any old data.
            return false;
        }

        $amountOfUnmigratedRecords = (int)$queryBuilder
            ->count('fe_users.uid')
            ->executeQuery()
            ->fetchOne();

        return $amountOfUnmigratedRecords > 0;
    }

    public function executeUpdate(): bool
    {
        $queryResult = $this->getUnmigratedRecordsQueryBuilder()
            ->select('fe_users.uid', 'fe_users.ip_address')
            ->executeQuery();

        $ipAddressConnection = $this->connectionPool->getConnectionForTable('tx_jwauth_domain_model_ipaddress');
        $mmConnection = $this->connectionPool->getConnectionForTable('tx_jwauth_fe_users_ipaddress_mm');

        while ($feUser = $queryResult->fetchAssociative()) {
            $ipAddressConnection->insert(
                'tx_jwauth_domain_model_ipaddress',
                [
                    // tx_jwauth_domain_model_ipaddress is rootLevel-only, so pid must be 0
                    // regardless of the fe_user's own storage folder.
                    'pid' => 0,
                    'ip_address' => (string)$feUser['ip_address'],
                ],
            );
            $newIpAddressUid = (int)$ipAddressConnection->lastInsertId();

            $mmConnection->insert(
                'tx_jwauth_fe_users_ipaddress_mm',
                [
                    'uid_local' => (int)$feUser['uid'],
                    'uid_foreign' => $newIpAddressUid,
                    'sorting' => 1,
                    'sorting_foreign' => 0,
                ],
            );

            $this->output?->writeln(sprintf(
                'Migrated fe_users:%d ip_address "%s".',
                $feUser['uid'],
                $feUser['ip_address'],
            ));
        }

        return true;
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    private function getUnmigratedRecordsQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('fe_users');
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        return $queryBuilder
            ->from('fe_users')
            ->leftJoin(
                'fe_users',
                'tx_jwauth_fe_users_ipaddress_mm',
                'mm',
                $queryBuilder->expr()->eq('mm.uid_local', $queryBuilder->quoteIdentifier('fe_users.uid')),
            )
            ->where(
                $queryBuilder->expr()->neq('fe_users.ip_address', $queryBuilder->createNamedParameter('')),
                $queryBuilder->expr()->isNull('mm.uid_local'),
            );
    }
}
