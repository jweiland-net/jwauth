<?php

/*
 * This file is part of the package jweiland/jwauth.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

return [
    'frontend' => [
        'jweiland/jwauth/clear-ip-authenticated-session' => [
            'target' => \JWeiland\Jwauth\Middleware\ClearIpAuthenticatedSessionMiddleware::class,
            'after' => [
                'typo3/cms-frontend/authentication',
            ],
        ],
    ],
];
