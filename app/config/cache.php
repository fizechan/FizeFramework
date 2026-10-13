<?php

/**
 * 缓存设置
 */
return [
    'handler' => 'File',
    'config'  => [
        'path'   => fize_runtime_path() . '/cache',
        'expire' => 0
    ]
];
