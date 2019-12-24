<?php

use fize\framework\App;

return [
    'handler' => 'File',
    'config'  => [
        'path'     => App::runtimePath() . '/log',
        'file'     => date('Ymd') . '.log',
        'max_size' => 2 * 1024 * 1024
    ]
];