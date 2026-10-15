<?php

/**
 * 视图设置
 */
return [
    'handler' => 'PHP',
    'config'  => [
        'view' => fize_view_path()
    ],
    'tpl'     => [
        'page_404'          => null,  // 404页面
        'page_500'          => null,  // 500页面
        'msg_error'         => null,  // 错误信息
        'msg_success'       => null,  // 成功信息
        'handler_error'     => null,  // 系统错误
        'handler_exception' => null,  // 系统异常
    ]
];
