<?php

/**
 * 缓存设置
 */
return [
    'handler' => 'File',
    'config'  => [
        'path'   => FIZE_RUNTIME_PATH . '/cache',
        'expire' => 0
    ]
];
