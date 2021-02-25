<?php
use fize\framework\App;
use fize\framework\Env;

return [
    'handler' => 'Twig',
    'config'  => [
        'view'  => App::module() ? Env::appPath() . '/' . App::module() . '/' . Env::appViewDir() : Env::appPath() . '/' . Env::appViewDir(),
        'cache' => Env::runtimePath() . '/view',
        'debug' => true
    ]
];