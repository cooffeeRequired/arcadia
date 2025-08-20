<?php

return [
    'name' => 'example',
    'display_name' => 'Příklad modulu',
    'description' => 'Tento modul slouží jako příklad struktury modulu pro Arcadia CRM',
    'version' => '1.0.0',
    'author' => 'Arcadia Team',
    'dependencies' => null,
    'permissions' => [
        'view' => ['admin', 'manager', 'user'],
        'create' => ['admin', 'manager'],
        'edit' => ['admin', 'manager'],
        'delete' => ['admin']
    ],
    'routes' => [
        'GET /example' => ['ExampleController@index', 'example.index'],
        'GET /example/create' => ['ExampleController@create', 'example.create'],
        'POST /example' => ['ExampleController@store', 'example.store'],
        'GET /example/{id}' => ['ExampleController@show', 'example.show'],
        'GET /example/{id}/edit' => ['ExampleController@edit', 'example.edit'],
        'PUT /example/{id}' => ['ExampleController@update', 'example.update'],
        'DELETE /example/{id}' => ['ExampleController@destroy', 'example.destroy']
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
    ],
    'controllers' => 'controllers',
    'entities' => 'entities',
    'views' => 'views',
    'migrations' => [
        'install' => 'install.php',
        'uninstall' => 'uninstall.php',
        '_' => '*.php'
    ]
];
