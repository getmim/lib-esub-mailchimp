<?php

return [
    '__name' => 'lib-esub-mailchimp',
    '__version' => '0.0.1',
    '__git' => 'git@github.com:getmim/lib-esub-mailchimp.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'http://iqbalfn.com/'
    ],
    '__files' => [
        'modules/lib-esub-mailchimp' => ['install','update','remove']
    ],
    '__dependencies' => [
        'required' => [
            [
                'lib-esub' => null
            ]
        ],
        'optional' => []
    ],
    '__inject' => [
        [
            'name' => 'libEsubMailchimp',
            'children' => [
                [
                    'name' => 'list',
                    'question' => 'Default list id',
                    'rules' => '!^.+$!'
                ],
                [
                    'name' => 'apikey',
                    'question' => 'API connector API Key',
                    'rules' => '!^.+$!'
                ]
            ]
        ]
    ],
    'autoload' => [
        'classes' => [
            'LibEsubMailchimp\\Library' => [
                'type' => 'file',
                'base' => 'modules/lib-esub-mailchimp/library'
            ],
            'DrewM\\MailChimp' => [
                'type' => 'psr4',
                'base' => 'modules/lib-esub-mailchimp/third-party/MailChimp'
            ]
        ],
        'files' => []
    ],
    'libEsub' => [
        'handler' => 'LibEsubMailchimp\\Library\\Mailchimp'
    ]
];