<?php
/** @noinspection PhpIncludeInspection */


use PHPUnit\Framework\TestCase;
use fize\framework\App;


class ControllerTest extends TestCase
{

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        //注册自动加载用于测试中加载控制器
        spl_autoload_register(function ($class_name) {
            $file_def = __DIR__ . str_replace('\\', DIRECTORY_SEPARATOR, "/{$class_name}.php");
            if (is_file($file_def)) {
                require_once $file_def;
            }
        });

        $config = [
            'root_path' => __DIR__,
            'module'    => 'test'
        ];
        new App($config);
    }

    public function testResult()
    {
        $_GET['_r'] = 'test/tresult';  //伪装路由
        $config = [
            'root_path' => __DIR__,
            'module'    => 'test'
        ];
        $app = new App($config);
        $app->run();
        self::assertTrue(true);
    }
}
