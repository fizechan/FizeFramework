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
        'page_404'          => null,  // 模板：404
        'msg_error'         => null,  // 模板：错误跳转
        'msg_success'       => null,  // 模板：成功跳转
        'handler_error'     => null,  // 模板：系统错误
        'handler_exception' => null,  // 模板：系统异常
    ]
];
