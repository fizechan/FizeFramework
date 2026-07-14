<?php

use Fize\Framework\Env;

/**
 * 日志设置
 */
return [
    'handler' => 'File',
    'config'  => [
        'path'     => Env::runtimePath() . '/log',
        'file'     => date('Ymd') . '.log',
        'max_size' => 2 * 1024 * 1024
    ]
];
