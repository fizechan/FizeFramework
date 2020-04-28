<?php

/**
 * SESSION设置
 */
return [
    'cache_expire'      => null,
    'cache_limiter'     => null,
    'module_name'       => null,
    'name'              => null,
    'register_shutdown' => null,
    'save_path'         => null,
    'cookie_params'     => [],
    'save_handler'      => [
        'type'              => '',
        'config'            => [],
        'register_shutdown' => true
    ]
];
