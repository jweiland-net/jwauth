<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'JW Auth',
    'description' => 'Login to TYPO3 frontend with your static IP address',
    'category' => 'plugin',
    'author' => 'Stefan Froemken',
    'author_email' => 'projects@jweiland.net',
    'state' => 'stable',
    'version' => '4.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.23-12.4.0',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
