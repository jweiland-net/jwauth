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
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ClearIpAuthenticatedSessionMiddlewareTest extends UnitTestCase
{
    protected ClearIpAuthenticatedSessionMiddleware $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new ClearIpAuthenticatedSessionMiddleware();
    }

    protected function tearDown(): void
    {
        unset($this->subject);

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
    public function processDoesNotLogOffFrontendUserOnMissingIpAddress(): void
    {
        $request = $this->getRequestWithFrontendUserAndRemoteAddress(['uid' => 1], '10.0.0.1');
        $frontendUser = $request->getAttribute('frontend.user');
        $frontendUser->expects(self::never())->method('logoff');

        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        self::assertSame($response, $this->subject->process($request, $handler));
    }

    #[Test]
    public function processDoesNotLogOffFrontendUserOnMismatchingIpAddress(): void
    {
        $request = $this->getRequestWithFrontendUserAndRemoteAddress(['uid' => 1, 'ip_address' => '10.0.0.1'], '10.0.0.2');
        $frontendUser = $request->getAttribute('frontend.user');
        $frontendUser->expects(self::never())->method('logoff');

        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        self::assertSame($response, $this->subject->process($request, $handler));
    }

    #[Test]
    public function processLogsOffFrontendUserOnMatchingIpAddress(): void
    {
        $request = $this->getRequestWithFrontendUserAndRemoteAddress(['uid' => 1, 'ip_address' => '10.0.0.1'], '10.0.0.1');
        $frontendUser = $request->getAttribute('frontend.user');
        $frontendUser->expects(self::once())->method('logoff');

        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        self::assertSame($response, $this->subject->process($request, $handler));
    }
}
