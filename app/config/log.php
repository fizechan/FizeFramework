<?php

/**
 * 日志设置
 */
return [
    'handler' => 'File',
    'config'  => [
        'path'     => '%runtime_path%/log',
        'file'     => date('Ymd') . '.log',
        'max_size' => 2 * 1024 * 1024
    ]
];
