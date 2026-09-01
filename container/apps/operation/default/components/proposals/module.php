<?php

return [
    'slug' => 'proposals',
    'name' => 'Propostas',
    'description' => 'Recebe e organiza solicitações comerciais enviadas pelo site.',
    'version' => '1.1.0',
    'permission' => 'proposals.manage',
    'menu' => [
        'label' => 'Propostas',
        'icon' => 'document-text-outline',
        'position' => 45,
    ],
    'routes' => [
        ['get', '/proposals', 'Operation:proposals'],
        ['get', '/proposals/{id}', 'Operation:proposal'],
        ['post', '/proposals/{id}', 'Operation:proposal'],
    ],
    'dashboard' => 'dashboard.php',
];
