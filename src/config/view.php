<?php

use fize\framework\App;

return [
    'driver' => 'Php',
    'config' => [
        'path' => App::module() ? App::appPath() . '/' . App::module() . '/view' : App::appPath() . '/view'
    ]
];