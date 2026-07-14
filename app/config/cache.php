<?php

use Fize\Framework\Env;

/**
 * 缓存设置
 */
return [
    'handler' => 'File',
    'config'  => [
        'path'   => Env::runtimePath() . '/cache',
        'expire' => 0
    ]
];
