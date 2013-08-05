<?php

return [

    'error_manager' =>
    [
        // Display code before and after identified line
        'precision'         => 10,

        // Limit of trace view, use true for all
        'display_trace'     => true,

        // Limit of previous exceptions, true for all
        'display_previous'  => true,

        // Show dockblock details
        'display_docblock'  => true,

        // Convert errors to ErrorException: set the Level to catch here
        'convert_error'     => E_ALL,

        // Extra assets
        'assets' =>
        [
            'css' => [
                //'//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css'
                'http://demo.stanleyhlng.com/prettify-js/assets/themes/bootstrap-light/prettify.css'
            ],
            'js'  =>
            [
                //'//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js'
                'http://google-code-prettify.googlecode.com/svn/trunk/src/prettify.js'
            ]
        ]
    ],

    'service_manager' =>
    [
        'invokables' =>
        [
            'errormanager.formatter.exception' => 'ErrorManager\Formatter\ExceptionFormatter',
            'errormanager.formatter.route'     => 'ErrorManager\Formatter\RouteFormatter',
        ],
        'factories'  =>
        [
            'errormanager.listener'            => 'ErrorManager\Factory',
        ]
    ],

    'view_manager' =>
    [
        'template_map' =>
        [
            'error/index' => __DIR__ . '/../view/error/exception.phtml',
            'error/404'   => __DIR__ . '/../view/error/404.phtml',
        ],
    ],

    'view_helpers' =>
    [
        'invokables' =>
        [
            'errTraceMethod'    => 'ErrorManager\View\TraceMethod',
            'errFileName'       => 'ErrorManager\View\FileName',
        ]
    ]

];