<?php

use fize\framework\App;

return [
    'handler' => 'Php',
    'config'  => [
        'path' => App::module() ? App::appPath() . '/' . App::module() . '/' . App::env('app_view_dir') : App::appPath() . '/' . App::env('app_view_dir')
    ]
];