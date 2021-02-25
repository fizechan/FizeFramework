<?php

use fize\framework\Env;

return [
    'handler' => 'File',
    'config'  => [
        'path'   => Env::runtimePath() . '/cache',
        'expire' => 0
    ]
];