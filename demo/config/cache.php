<?php

use Fize\Framework\Env;

return [
    'handler' => 'File',
    'config'  => [
        'path'   => Env::runtimePath() . '/cache',
        'expire' => 0
    ]
];