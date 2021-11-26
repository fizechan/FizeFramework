<?php

namespace Fize\Framework;

require __DIR__ . '/../vendor/autoload.php';

/**
 * 注册自动加载用于测试中加载控制器
 */
function autoload_register()
{
    spl_autoload_register(function ($class_name) {
        $file_def = __DIR__ . str_replace('\\', DIRECTORY_SEPARATOR, "/{$class_name}.php");
        if (is_file($file_def)) {
            require_once $file_def;
        }
    });
}
autoload_register();

$app = new App(
    [
        'root_path' => __DIR__,
        'debug'     => true
    ]
);
$app->run();
