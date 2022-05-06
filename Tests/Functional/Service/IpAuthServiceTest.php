<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/jwauth.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Jwauth\Tests\Functional\Service;

use JWeiland\Jwauth\Service\IpAuthService;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Functional test for IpAuthService
 */
class IpAuthServiceTest extends FunctionalTestCase
{
    use ProphecyTrait;

    /**
     * @var IpAuthService
     */
    protected $subject;

    /**
     * @var FrontendUserAuthentication|ObjectProphecy
     */
    protected $frontendUserAuthenticationProphecy;

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/jwauth'
    ];

    /**
     * @var array
     */
    protected $authInfo = [
        'db_user' => [
            'check_pid_clause' => 'deleted = 0'
        ],
        'REMOTE_ADDR' => ''
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->frontendUserAuthenticationProphecy = $this->prophesize(FrontendUserAuthentication::class);
        $this->importDataSet(__DIR__ . '/../Fixtures/fe_users.xml');

        $this->subject = new IpAuthService();
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->frontendUserAuthenticationProphecy
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function initReturnsTrue(): void
    {
        self::assertTrue(
            $this->subject->init()
        );
    }

    /**
     * @test
     */
    public function getUserReturnsEmptyArray(): void
    {
        self::assertSame(
            [],
            $this->subject->getUser()
        );
    }

    /**
     * @test
     */
    public function getUserWithNonMatchingIpAddressReturnsEmptyArray(): void
    {
        $authInfo = $this->authInfo;
        $authInfo['REMOTE_ADDR'] = '8.8.8.8';
        $this->subject->initAuth(
            '',
            [],
            $authInfo,
            $this->frontendUserAuthenticationProphecy->reveal()
        );

        self::assertSame(
            [],
            $this->subject->getUser()
        );
    }

    /**
     * @test
     */
    public function getUserWithMatchingIpAddressReturnsUserArray(): void
    {
        $authInfo = $this->authInfo;
        $authInfo['REMOTE_ADDR'] = '192.168.100.123';
        $this->subject->initAuth(
            '',
            [],
            $authInfo,
            $this->frontendUserAuthenticationProphecy->reveal()
        );

        $matchedUser = $this->subject->getUser();
        self::assertSame(
            2,
            $matchedUser['uid']
        );
        self::assertSame(
            'FullIPv4',
            $matchedUser['username']
        );
    }

    /**
     * @test
     */
    public function getUserWithPartlyMatchingIpAddressReturnsUserArray(): void
    {
        $authInfo = $this->authInfo;
        $authInfo['REMOTE_ADDR'] = '192.168.54.24';
        $this->subject->initAuth(
            '',
            [],
            $authInfo,
            $this->frontendUserAuthenticationProphecy->reveal()
        );

        $matchedUser = $this->subject->getUser();
        self::assertSame(
            3,
            $matchedUser['uid']
        );
        self::assertSame(
            'PartialIPv4',
            $matchedUser['username']
        );
    }

    /**
     * @test
     */
    public function getUserWithVeryPartlyMatchingIpAddressReturnsUserArray(): void
    {
        $authInfo = $this->authInfo;
        $authInfo['REMOTE_ADDR'] = '192.231.43.123';
        $this->subject->initAuth(
            '',
            [],
            $authInfo,
            $this->frontendUserAuthenticationProphecy->reveal()
        );

        $matchedUser = $this->subject->getUser();
        self::assertSame(
            4,
            $matchedUser['uid']
        );
        self::assertSame(
            'VeryPartialIPv4',
            $matchedUser['username']
        );
    }

    /**
     * @test
     */
    public function getUserWithMatchingIpv6AddressReturnsUserArray(): void
    {
        $authInfo = $this->authInfo;
        $authInfo['REMOTE_ADDR'] = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';
        $this->subject->initAuth(
            '',
            [],
            $authInfo,
            $this->frontendUserAuthenticationProphecy->reveal()
        );

        $matchedUser = $this->subject->getUser();
        self::assertSame(
            5,
            $matchedUser['uid']
        );
        self::assertSame(
            'IPv6',
            $matchedUser['username']
        );
    }

    /**
     * @test
     */
    public function getUserWithPartlyMatchingIpv6AddressReturnsUserArray(): void
    {
        $authInfo = $this->authInfo;
        $authInfo['REMOTE_ADDR'] = '2001:0db8:85a3:8a2e:0370:7334:3481:a4b2';
        $this->subject->initAuth(
            '',
            [],
            $authInfo,
            $this->frontendUserAuthenticationProphecy->reveal()
        );

        $matchedUser = $this->subject->getUser();

        self::assertSame(
            6,
            $matchedUser['uid']
        );
        self::assertSame(
            'PartlyIPv6',
            $matchedUser['username']
        );
    }
}
