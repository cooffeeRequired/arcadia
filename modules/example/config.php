<?php

return [
    'name' => 'example',
    'display_name' => 'Příklad modulu',
    'description' => 'Tento modul slouží jako příklad struktury modulu pro Arcadia CRM',
    'version' => '1.0.0',
    'author' => 'Arcadia Team',
    'dependencies' => [
        'core' // Závisí na základním modulu
    ],
    'settings' => [
        'enabled' => true,
        'debug_mode' => false,
        'max_items' => 100
    ],
    'permissions' => [
        'view' => ['admin', 'manager', 'user'],
        'create' => ['admin', 'manager'],
        'edit' => ['admin', 'manager'],
        'delete' => ['admin']
    ],
    'routes' => [
        'GET /example' => 'ExampleController@index',
        'GET /example/create' => 'ExampleController@create',
        'POST /example' => 'ExampleController@store',
        'GET /example/{id}' => 'ExampleController@show',
        'GET /example/{id}/edit' => 'ExampleController@edit',
        'PUT /example/{id}' => 'ExampleController@update',
        'DELETE /example/{id}' => 'ExampleController@destroy'
    ],
    'menu' => [
        'title' => 'Příklad',
        'icon' => 'fas fa-cube',
        'order' => 10,
        'children' => [
            [
                'title' => 'Seznam',
                'url' => '/example',
                'permission' => 'view'
            ],
            [
                'title' => 'Nový',
                'url' => '/example/create',
                'permission' => 'create'
            ]
        ]
    ]
];
