<?php

use Fize\Framework\App;
use Fize\Framework\Env;

/**
 * 视图设置
 */
return [
    'handler'               => 'Php',
    'config'                => [
        'view' => App::module() ? Env::appPath() . 'view/' . App::module() . '/' . Env::appViewDir() : Env::appPath() . 'view/' . Env::appViewDir()
    ],
    'tpl_404'               => null,  // 模板：404
    'tpl_error'             => null,  // 模板：错误跳转
    'tpl_success'           => null,  // 模板：成功跳转
    'tpl_error_handler'     => null,  // 模板：系统错误
    'tpl_exception_handler' => null,  // 模板：系统异常
];
