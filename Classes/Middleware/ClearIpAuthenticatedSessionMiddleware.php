<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/jwauth.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Jwauth\Middleware;

use JWeiland\Jwauth\Service\IpAddressMatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * jwauth re-authenticates a visitor by IP address on every single request. If the
 * fe_users session was allowed to persist like a regular login, deactivating jwauth
 * or removing a fe_user's IP addresses would not immediately revoke access, as the
 * visitor would still be considered logged in through the leftover session. Logging
 * the user off again right after the response has been built ensures the IP
 * addresses are re-checked on every request.
 */
final class ClearIpAuthenticatedSessionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly IpAddressMatcher $ipAddressMatcher,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $frontendUser = $request->getAttribute('frontend.user');
        $remoteAddress = $request->getAttribute('normalizedParams')?->getRemoteAddress() ?? '';

        if (
            $frontendUser instanceof FrontendUserAuthentication
            && is_array($frontendUser->user)
            && ($frontendUser->user['uid'] ?? 0) > 0
            && $this->ipAddressMatcher->userHasMatchingIpAddress((int)$frontendUser->user['uid'], $remoteAddress)
        ) {
            $frontendUser->logoff();
        }

        return $response;
    }
}
