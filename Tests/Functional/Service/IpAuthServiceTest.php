<?php
namespace JWeiland\Jwauth\Tests\Functional\Service;

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

use JWeiland\Jwauth\Service\IpAuthService;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Functional test for IpAuthService
 */
class IpAuthServiceTest extends FunctionalTestCase
{
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

    public function setUp()
    {
        parent::setUp();
        $this->frontendUserAuthenticationProphecy = $this->prophesize(FrontendUserAuthentication::class);
        $this->importDataSet(__DIR__ . '/../Fixtures/fe_users.xml');
        $this->subject = new IpAuthService();
    }

    public function tearDown()
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
    public function initReturnsTrue()
    {
        $this->assertTrue(
            $this->subject->init()
        );
    }

    /**
     * @test
     */
    public function getUserReturnsEmptyArray()
    {
        $this->assertSame(
            [],
            $this->subject->getUser()
        );
    }

    /**
     * @test
     */
    public function getUserWithNonMatchingIpAddressReturnsEmptyArray()
    {
        $authInfo = $this->authInfo;
        $authInfo['REMOTE_ADDR'] = '8.8.8.8';
        $this->subject->initAuth(
            '',
            [],
            $authInfo,
            $this->frontendUserAuthenticationProphecy->reveal()
        );

        $this->assertSame(
            [],
            $this->subject->getUser()
        );
    }

    /**
     * @test
     */
    public function getUserWithMatchingIpAddressReturnsUserArray()
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
        $this->assertSame(
            2,
            $matchedUser['uid']
        );
        $this->assertSame(
            'FullIPv4',
            $matchedUser['username']
        );
    }

    /**
     * @test
     */
    public function getUserWithPartlyMatchingIpAddressReturnsUserArray()
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
        $this->assertSame(
            3,
            $matchedUser['uid']
        );
        $this->assertSame(
            'PartialIPv4',
            $matchedUser['username']
        );
    }

    /**
     * @test
     */
    public function getUserWithVeryPartlyMatchingIpAddressReturnsUserArray()
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
        $this->assertSame(
            4,
            $matchedUser['uid']
        );
        $this->assertSame(
            'VeryPartialIPv4',
            $matchedUser['username']
        );
    }

    /**
     * @test
     */
    public function getUserWithMatchingIpv6AddressReturnsUserArray()
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
        $this->assertSame(
            5,
            $matchedUser['uid']
        );
        $this->assertSame(
            'IPv6',
            $matchedUser['username']
        );
    }

    /**
     * @test
     */
    public function getUserWithPartlyMatchingIpv6AddressReturnsUserArray()
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
        $this->assertSame(
            6,
            $matchedUser['uid']
        );
        $this->assertSame(
            'PartlyIPv6',
            $matchedUser['username']
        );
    }
}
