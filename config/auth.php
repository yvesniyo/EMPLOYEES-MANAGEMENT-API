<?php

return [
    'defaults' => [
        'guard' => 'api',
        'passwords' => 'employees',
    ],

    'guards' => [
        'api' => [
            'driver' => 'jwt',
            'provider' => 'employees',
        ],
    ],

    'providers' => [
        'employees' => [
            'driver' => 'eloquent',
            'model' => \App\Models\Employee::class
        ]
    ]
];
