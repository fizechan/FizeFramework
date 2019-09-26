<?php

use fize\framework\App;

return [
    'driver' => 'Twig',
    'config' => [
        'path'  => App::module() ? App::appPath() . '/' . App::module() . '/view' : App::appPath() . '/view',
        'cache' => App::runtimePath() . '/view',
        'debug' => false
    ]
];