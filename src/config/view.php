<?php

use fize\framework\App;

return [
    'handler' => 'Php',
    'config'  => [
        'path' => App::module() ? App::appPath() . '/' . App::module() . '/view' : App::appPath() . '/view'
    ]
];