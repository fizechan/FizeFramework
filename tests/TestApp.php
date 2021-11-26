<?php

namespace Tests;

use Fize\Framework\App;
use Fize\Web\Request;
use PHPUnit\Framework\TestCase;

class TestApp extends TestCase
{

    /**
     * @var App
     */
    protected $app;

    /**
     * 注册自动加载用于测试中加载控制器
     * @param null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $config = [
            'root_path' => dirname(__DIR__) . '/examples',
            'module'    => 'test'
        ];

        spl_autoload_register(function ($class_name) use ($config) {
            $file_def = $config['root_path'] . str_replace('\\', DIRECTORY_SEPARATOR, "/{$class_name}.php");
            if (is_file($file_def)) {
                require_once $file_def;
            }
        });

        $this->app = new App($config);
    }

    public function testRun()
    {
        $_GET['_r'] = 'index/test';  //伪装路由
        $config = [
            'root_path' => dirname(__DIR__) . '/examples',
            'module'    => 'test'
        ];
        $app = new App($config);
        $app->run();
        self::assertTrue(true);
    }

    public function testRoute()
    {
        $path_info = '/index/index';
        $route = $path_info;
        if ($route) {
            $route = substr($route, 1);  //删除第一个字符'/'
        } else {
            $route = Request::get('_r');
        }
        var_dump($route);

        self::assertEquals('index/index', $route);
    }

    public function testModule()
    {
        $module = App::module();
        self::assertEquals('test', $module);
    }

    public function testAction()
    {
        $_GET['_r'] = 'index/test2';  //伪装路由

        $config = [
            'root_path' => dirname(__DIR__) . '/temp',
            'module'    => 'test'
        ];
        new App($config);

        $action = App::action();
        var_dump($action);
        self::assertEquals('test2', $action);
    }

    public function testController()
    {
        $_GET['_r'] = 'index/test2';  //伪装路由

        $config = [
            'root_path' => dirname(__DIR__) . '/temp',
            'module'    => 'test'
        ];
        new App($config);

        $controller = App::controller();
        self::assertEquals('Index', $controller);
    }
}
