<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/jwauth.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Jwauth\Tests\Functional\Upgrade;

use JWeiland\Jwauth\Service\IpAddressMatcher;
use JWeiland\Jwauth\Upgrade\MigrateIpAddressToMMUpgrade;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Install\Service\UpgradeWizardsService;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class MigrateIpAddressToMMUpgradeTest extends FunctionalTestCase
{
    protected MigrateIpAddressToMMUpgrade $subject;

    protected array $coreExtensionsToLoad = [
        'install',
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/jwauth',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // Schema-compare (run by parent::setUp() from current TCA) no longer knows about
        // the legacy fe_users.ip_address column, so we add it back here to simulate the
        // state of a database that has just been upgraded from a pre-5.0.0 jwauth version.
        // TestingFramework reuses the same schema across all tests of this class, so this
        // must be idempotent.
        $connection = $this->get(ConnectionPool::class)->getConnectionForTable('fe_users');
        if (!array_key_exists('ip_address', $connection->createSchemaManager()->listTableColumns('fe_users'))) {
            $connection->executeStatement("ALTER TABLE fe_users ADD ip_address VARCHAR(43) DEFAULT '' NOT NULL");
        }

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/fe_users_legacy_ip_address.csv');

        $this->subject = new MigrateIpAddressToMMUpgrade($this->get(ConnectionPool::class));
    }

    protected function tearDown(): void
    {
        unset($this->subject);

        parent::tearDown();
    }

    #[Test]
    public function updateNecessaryReturnsTrueWhenLegacyColumnHasUnmigratedValues(): void
    {
        self::assertTrue($this->subject->updateNecessary());
    }

    #[Test]
    public function updateNecessaryReturnsFalseWhenAllLegacyValuesAlreadyMigrated(): void
    {
        $this->subject->executeUpdate();

        self::assertFalse($this->subject->updateNecessary());
    }

    #[Test]
    public function executeUpdateCreatesOneIpAddressRowAndOneMmRowPerLegacyValue(): void
    {
        self::assertTrue($this->subject->executeUpdate());

        $connectionPool = $this->get(ConnectionPool::class);

        $amountOfIpAddresses = (int)$connectionPool
            ->getQueryBuilderForTable('tx_jwauth_domain_model_ipaddress')
            ->count('uid')
            ->from('tx_jwauth_domain_model_ipaddress')
            ->executeQuery()
            ->fetchOne();
        // 6 fe_users in the fixture, 1 of them (NoIP) has an empty ip_address and must be skipped.
        self::assertSame(5, $amountOfIpAddresses);

        $amountOfMmRows = (int)$connectionPool
            ->getQueryBuilderForTable('tx_jwauth_fe_users_ipaddress_mm')
            ->count('uid_local')
            ->from('tx_jwauth_fe_users_ipaddress_mm')
            ->executeQuery()
            ->fetchOne();
        self::assertSame(5, $amountOfMmRows);
    }

    #[Test]
    public function executeUpdateSkipsFeUserWithEmptyLegacyIpAddress(): void
    {
        $this->subject->executeUpdate();

        $queryBuilder = $this->get(ConnectionPool::class)->getQueryBuilderForTable('tx_jwauth_fe_users_ipaddress_mm');
        $amountOfMmRowsForNoIpUser = (int)$queryBuilder
            ->count('uid_local')
            ->from('tx_jwauth_fe_users_ipaddress_mm')
            ->where($queryBuilder->expr()->eq('uid_local', 1))
            ->executeQuery()
            ->fetchOne();

        self::assertSame(0, $amountOfMmRowsForNoIpUser);
    }

    #[Test]
    public function executeUpdateIsIdempotentWhenRunTwice(): void
    {
        $this->subject->executeUpdate();
        $this->subject->executeUpdate();

        $amountOfIpAddresses = (int)$this->get(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_jwauth_domain_model_ipaddress')
            ->count('uid')
            ->from('tx_jwauth_domain_model_ipaddress')
            ->executeQuery()
            ->fetchOne();

        self::assertSame(5, $amountOfIpAddresses);
    }

    /**
     * Proves the wizard is actually resolvable the same way the Install Tool resolves it
     * (attribute-based registration, no Configuration/Services.yaml entry needed for it).
     */
    #[Test]
    public function wizardIsRegisteredUnderItsAttributeIdentifier(): void
    {
        $wizardInformation = $this->get(UpgradeWizardsService::class)
            ->getWizardInformationByIdentifier('jwauth_migrateIpAddress');

        self::assertSame(MigrateIpAddressToMMUpgrade::class, $wizardInformation['class']);
        self::assertSame($this->subject->getTitle(), $wizardInformation['title']);
    }

    /**
     * Regression test: matching behaviour for every legacy scenario must be identical
     * before and after migration.
     */
    #[Test]
    public function migratedDataMatchesSameRemoteAddressesAsBeforeMigration(): void
    {
        $this->subject->executeUpdate();

        $ipAddressMatcher = new IpAddressMatcher($this->get(ConnectionPool::class));

        self::assertTrue($ipAddressMatcher->userHasMatchingIpAddress(2, '192.168.100.123'));
        self::assertTrue($ipAddressMatcher->userHasMatchingIpAddress(3, '192.168.54.24'));
        self::assertTrue($ipAddressMatcher->userHasMatchingIpAddress(4, '192.231.43.123'));
        self::assertTrue($ipAddressMatcher->userHasMatchingIpAddress(5, '2001:0db8:85a3:0000:0000:8a2e:0370:7334'));
        self::assertTrue($ipAddressMatcher->userHasMatchingIpAddress(6, '2001:0db8:85a3:8a2e:0370:7334:3481:a4b2'));
        self::assertFalse($ipAddressMatcher->userHasMatchingIpAddress(1, '8.8.8.8'));
    }
}
