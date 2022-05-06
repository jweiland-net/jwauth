<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'JW Auth',
    'description' => 'Login to TYPO3 backend with your static IP address',
    'category' => 'plugin',
    'author' => 'Stefan Froemken',
    'author_email' => 'projects@jweiland.net',
    'state' => 'stable',
    'version' => '3.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.19-11.5.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
