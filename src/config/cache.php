<?php

use fize\framework\Env;

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
