<?php

use fize\framework\App;

return [
    'handler' => 'Php',
    'config'  => [
        'view' => App::module() ? App::appPath() . '/' . App::module() . '/' . App::env('app_view_dir') : App::appPath() . '/' . App::env('app_view_dir')
    ],
    'dispatch_success_tmpl' => null,  //默认成功跳转对应的模板文件
    'dispatch_error_tmpl' => null,  //默认错误跳转对应的模板文件
];