<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'JW Auth',
    'description' => 'Login to TYPO3 frontend with your static IP address',
    'category' => 'plugin',
    'author' => 'Stefan Froemken',
    'author_email' => 'projects@jweiland.net',
    'state' => 'stable',
    'version' => '3.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.36-11.5.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
