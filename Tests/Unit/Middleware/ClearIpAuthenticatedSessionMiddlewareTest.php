<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/jwauth.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Jwauth\Tests\Unit\Middleware;

use JWeiland\Jwauth\Middleware\ClearIpAuthenticatedSessionMiddleware;
use JWeiland\Jwauth\Service\IpAddressMatcher;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ClearIpAuthenticatedSessionMiddlewareTest extends UnitTestCase
{
    protected ClearIpAuthenticatedSessionMiddleware $subject;

    /**
     * @var IpAddressMatcher|MockObject
     */
    protected $ipAddressMatcherMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ipAddressMatcherMock = $this->createMock(IpAddressMatcher::class);
        $this->subject = new ClearIpAuthenticatedSessionMiddleware($this->ipAddressMatcherMock);
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->ipAddressMatcherMock,
        );

        parent::tearDown();
    }

    private function getRequestWithFrontendUserAndRemoteAddress(?array $user, string $remoteAddress): ServerRequestInterface
    {
        $frontendUser = $this->createMock(FrontendUserAuthentication::class);
        $frontendUser->user = $user;

        $normalizedParams = $this->createMock(NormalizedParams::class);
        $normalizedParams->method('getRemoteAddress')->willReturn($remoteAddress);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturnMap([
            ['frontend.user', null, $frontendUser],
            ['normalizedParams', null, $normalizedParams],
        ]);

        return $request;
    }

    #[Test]
    public function processDoesNotLogOffFrontendUserWithoutUid(): void
    {
        $request = $this->getRequestWithFrontendUserAndRemoteAddress(['uid' => 0], '10.0.0.1');
        $frontendUser = $request->getAttribute('frontend.user');
        $frontendUser->expects(self::never())->method('logoff');

        $this->ipAddressMatcherMock->expects(self::never())->method('userHasMatchingIpAddress');

        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        self::assertSame($response, $this->subject->process($request, $handler));
    }

    #[Test]
    public function processDoesNotLogOffFrontendUserWhenNoIpAddressMatches(): void
    {
        $request = $this->getRequestWithFrontendUserAndRemoteAddress(['uid' => 1], '10.0.0.2');
        $frontendUser = $request->getAttribute('frontend.user');
        $frontendUser->expects(self::never())->method('logoff');

        $this->ipAddressMatcherMock->method('userHasMatchingIpAddress')->with(1, '10.0.0.2')->willReturn(false);

        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        self::assertSame($response, $this->subject->process($request, $handler));
    }

    #[Test]
    public function processLogsOffFrontendUserWhenAnyIpAddressMatches(): void
    {
        $request = $this->getRequestWithFrontendUserAndRemoteAddress(['uid' => 1], '10.0.0.1');
        $frontendUser = $request->getAttribute('frontend.user');
        $frontendUser->expects(self::once())->method('logoff');

        $this->ipAddressMatcherMock->method('userHasMatchingIpAddress')->with(1, '10.0.0.1')->willReturn(true);

        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        self::assertSame($response, $this->subject->process($request, $handler));
    }
}
