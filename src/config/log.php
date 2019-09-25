<?php

use fize\framework\App;

return [
    'driver' => 'File',
    'config' => [
        'path'   => App::runtimePath() . '/log'
    ]
];