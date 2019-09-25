<?php

use fize\framework\App;

return [
    'driver' => 'File',
    'config' => [
        'path'   => App::runtimePath() . '/cache',
        'expire' => 0
    ]
];