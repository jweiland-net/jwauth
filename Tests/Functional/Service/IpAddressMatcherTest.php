<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/jwauth.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Jwauth\Tests\Functional\Service;

use JWeiland\Jwauth\Service\IpAddressMatcher;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class IpAddressMatcherTest extends FunctionalTestCase
{
    protected IpAddressMatcher $subject;

    protected array $testExtensionsToLoad = [
        'jweiland/jwauth',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/fe_users.csv');

        $this->subject = new IpAddressMatcher($this->get(ConnectionPool::class));
    }

    protected function tearDown(): void
    {
        unset($this->subject);

        parent::tearDown();
    }

    #[Test]
    public function userHasMatchingIpAddressReturnsFalseForUserWithoutIpAddresses(): void
    {
        self::assertFalse(
            $this->subject->userHasMatchingIpAddress(1, '192.168.100.123'),
        );
    }

    #[Test]
    public function userHasMatchingIpAddressReturnsTrueForMatchingIpAddress(): void
    {
        self::assertTrue(
            $this->subject->userHasMatchingIpAddress(2, '192.168.100.123'),
        );
    }

    #[Test]
    public function userHasMatchingIpAddressReturnsFalseForNonMatchingIpAddress(): void
    {
        self::assertFalse(
            $this->subject->userHasMatchingIpAddress(2, '8.8.8.8'),
        );
    }

    #[Test]
    public function userHasMatchingIpAddressReturnsTrueWhenEitherOfTwoIpAddressesMatches(): void
    {
        self::assertTrue(
            $this->subject->userHasMatchingIpAddress(7, '203.0.113.10'),
        );
        self::assertTrue(
            $this->subject->userHasMatchingIpAddress(7, '203.0.113.20'),
        );
    }

    #[Test]
    public function userHasMatchingIpAddressReturnsFalseForHiddenIpAddress(): void
    {
        self::assertFalse(
            $this->subject->userHasMatchingIpAddress(8, '1.2.3.4'),
        );
    }
}
