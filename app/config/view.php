<?php

/**
 * 视图设置
 */
return [
    'handler' => 'PHP',
    'config'  => [
        'view' => '%module_path%/%app_view_dir%'
    ],
    'tpl'     => [
        'tpl_404'               => null,  // 模板：404
        'tpl_error'             => null,  // 模板：错误跳转
        'tpl_success'           => null,  // 模板：成功跳转
        'tpl_error_handler'     => null,  // 模板：系统错误
        'tpl_exception_handler' => null,  // 模板：系统异常
    ]
];
