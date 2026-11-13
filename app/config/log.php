<?php

/**
 * 日志设置
 */
return [
    'handler' => 'File',
    'config'  => [
        'path'     => FIZE_RUNTIME_PATH . '/log',
        'file'     => date('Ymd') . '.log',
        'max_size' => 2 * 1024 * 1024
    ]
];
