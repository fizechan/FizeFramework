<?php

use fize\framework\App;

/**
 * 缓存设置
 */
return [
    'handler' => 'File',
    'config'  => [
        'path'   => App::runtimePath() . '/cache',
        'expire' => 0
    ]
];
