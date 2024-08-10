<?php

return [
    'add' => [
        'app',    // Add your project directories here
        'src',
        'tests',
    ],
    'exclude' => [
        'vendor',
        'node_modules',
        'public',
    ],
    'common_path' => ['src'],
    'preset' => 'default',
    'config' => [
        'rules' => [
            'Architecture' => [
                'directories' => ['src'],
            ],
            'Code' => [
                'rules' => [
                    'class_implements' => [
                        'methods' => true,
                        'properties' => true,
                    ],
                    'class_properties' => true,
                    'class_methods' => true,
                    'method_length' => true,
                    'complexity' => true,
                    'function_declaration' => true,
                    'function_length' => true,
                    'function_calls' => true,
                ],
            ],
            'Design' => [
                'rules' => [
                    'name' => true,
                    'interfaces' => true,
                    'final' => true,
                ],
            ],
        ],
    ],
];
