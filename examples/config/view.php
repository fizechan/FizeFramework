<?php
use fize\framework\App;

return [
    'handler' => 'Twig',
    'config'  => [
        'view'  => App::module() ? App::appPath() . '/' . App::module() . '/' . App::env('app_view_dir') : App::appPath() . '/' . App::env('app_view_dir'),
        'cache' => App::runtimePath() . '/view',
        'debug' => true
    ]
];