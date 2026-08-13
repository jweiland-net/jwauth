<?php

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
