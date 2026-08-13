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
use JWeiland\Jwauth\Service\IpAuthService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Functional test for IpAuthService
 */
class IpAuthServiceTest extends FunctionalTestCase
{
    protected IpAuthService $subject;

    /**
     * @var FrontendUserAuthentication|MockObject
     */
    protected $frontendUserAuthenticationMock;

    protected array $testExtensionsToLoad = [
        'jweiland/jwauth',
    ];

    protected array $authInfo = [
        'db_user' => [
            'check_pid_clause' => 'deleted = 0',
        ],
        'REMOTE_ADDR' => '',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->frontendUserAuthenticationMock = $this->createMock(FrontendUserAuthentication::class);
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/fe_users.csv');

        $this->subject = new IpAuthService(
            $this->get(ConnectionPool::class),
            new IpAddressMatcher($this->get(ConnectionPool::class)),
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->frontendUserAuthenticationMock,
        );

        parent::tearDown();
    }

    #[Test]
    public function initReturnsTrue(): void
    {
        self::assertTrue(
            $this->subject->init(),
        );
    }

    /**
     * Proves IpAuthService (public: true in Services.yaml) is resolvable through the
     * container the same way GeneralUtility::makeInstanceService() resolves it, including
     * its now-autowired IpAddressMatcher dependency (which needs no Services.yaml entry).
     */
    #[Test]
    public function serviceIsResolvableThroughContainer(): void
    {
        self::assertInstanceOf(
            IpAuthService::class,
            $this->get(IpAuthService::class),
        );
    }

    #[Test]
    public function getUserReturnsEmptyArray(): void
    {
        self::assertSame(
            [],
            $this->subject->getUser(),
        );
    }

    #[Test]
    public function getUserWithNonMatchingIpAddressReturnsNull(): void
    {
        $authInfo = $this->authInfo;
        $authInfo['REMOTE_ADDR'] = '8.8.8.8';
        $this->subject->initAuth(
            '',
            [],
            $authInfo,
            $this->frontendUserAuthenticationMock,
        );

        self::assertNull(
            $this->subject->getUser(),
        );
    }

    #[Test]
    public function getUserWithMatchingIpAddressReturnsUserArray(): void
    {
        $authInfo = $this->authInfo;
        $authInfo['REMOTE_ADDR'] = '192.168.100.123';
        $this->subject->initAuth(
            '',
            [],
            $authInfo,
            $this->frontendUserAuthenticationMock,
        );

        $matchedUser = $this->subject->getUser();
        self::assertSame(
            2,
            $matchedUser['uid'],
        );
        self::assertSame(
            'FullIPv4',
            $matchedUser['username'],
        );
    }

    #[Test]
    public function getUserWithPartlyMatchingIpAddressReturnsUserArray(): void
    {
        $authInfo = $this->authInfo;
        $authInfo['REMOTE_ADDR'] = '192.168.54.24';
        $this->subject->initAuth(
            '',
            [],
            $authInfo,
            $this->frontendUserAuthenticationMock,
        );

        $matchedUser = $this->subject->getUser();
        self::assertSame(
            3,
            $matchedUser['uid'],
        );
        self::assertSame(
            'PartialIPv4',
            $matchedUser['username'],
        );
    }

    #[Test]
    public function getUserWithVeryPartlyMatchingIpAddressReturnsUserArray(): void
    {
        $authInfo = $this->authInfo;
        $authInfo['REMOTE_ADDR'] = '192.231.43.123';
        $this->subject->initAuth(
            '',
            [],
            $authInfo,
            $this->frontendUserAuthenticationMock,
        );

        $matchedUser = $this->subject->getUser();
        self::assertSame(
            4,
            $matchedUser['uid'],
        );
        self::assertSame(
            'VeryPartialIPv4',
            $matchedUser['username'],
        );
    }

    #[Test]
    public function getUserWithMatchingIpv6AddressReturnsUserArray(): void
    {
        $authInfo = $this->authInfo;
        $authInfo['REMOTE_ADDR'] = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';
        $this->subject->initAuth(
            '',
            [],
            $authInfo,
            $this->frontendUserAuthenticationMock,
        );

        $matchedUser = $this->subject->getUser();
        self::assertSame(
            5,
            $matchedUser['uid'],
        );
        self::assertSame(
            'IPv6',
            $matchedUser['username'],
        );
    }

    #[Test]
    public function getUserWithPartlyMatchingIpv6AddressReturnsUserArray(): void
    {
        $authInfo = $this->authInfo;
        $authInfo['REMOTE_ADDR'] = '2001:0db8:85a3:8a2e:0370:7334:3481:a4b2';
        $this->subject->initAuth(
            '',
            [],
            $authInfo,
            $this->frontendUserAuthenticationMock,
        );

        $matchedUser = $this->subject->getUser();

        self::assertSame(
            6,
            $matchedUser['uid'],
        );
        self::assertSame(
            'PartlyIPv6',
            $matchedUser['username'],
        );
    }

    #[Test]
    public function getUserWithEitherOfTwoIpAddressesForSameUserReturnsThatUser(): void
    {
        foreach (['203.0.113.10', '203.0.113.20'] as $remoteAddress) {
            $authInfo = $this->authInfo;
            $authInfo['REMOTE_ADDR'] = $remoteAddress;
            $this->subject->initAuth(
                '',
                [],
                $authInfo,
                $this->frontendUserAuthenticationMock,
            );

            $matchedUser = $this->subject->getUser();
            self::assertSame(
                7,
                $matchedUser['uid'],
            );
            self::assertSame(
                'MultiIp',
                $matchedUser['username'],
            );
        }
    }

    #[Test]
    public function authUserReturns200WhenIpAddressMatches(): void
    {
        $authInfo = $this->authInfo;
        $authInfo['REMOTE_ADDR'] = '192.168.100.123';
        $this->subject->initAuth(
            '',
            [],
            $authInfo,
            $this->frontendUserAuthenticationMock,
        );

        self::assertSame(
            200,
            $this->subject->authUser(['uid' => 2]),
        );
    }

    #[Test]
    public function authUserReturns100WhenNoIpAddressMatches(): void
    {
        $authInfo = $this->authInfo;
        $authInfo['REMOTE_ADDR'] = '8.8.8.8';
        $this->subject->initAuth(
            '',
            [],
            $authInfo,
            $this->frontendUserAuthenticationMock,
        );

        self::assertSame(
            100,
            $this->subject->authUser(['uid' => 2]),
        );
    }
}
