<?php


use fize\framework\App;
use fize\web\Request;
use PHPUnit\Framework\TestCase;

class AppTest extends TestCase
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
     * @noinspection PhpIncludeInspection
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $config = [
            'root_path' => dirname(__DIR__) . '/temp',
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

    public function testRoute()
    {
        $PATH_INFO = '/index/index';
        $route = $PATH_INFO;
        if ($route) {
            $route = substr($route, 1);  //删除第一个字符'/'
        } else {
            $route = Request::get('_r');
        }
        var_dump($route);

        self::assertEquals($route, 'index/index');
    }

    public function testEnv()
    {
        $env = App::env();
        var_dump($env);
        self::assertIsArray($env);

        $root_path = App::env('root_path');
        var_dump($root_path);
        self::assertEquals($root_path, dirname(__DIR__) . '/temp');
    }

    public function testRun()
    {
        $_GET['_r'] = 'index/test';  //伪装路由
        $config = [
            'root_path' => dirname(__DIR__) . '/temp',
            'module'    => 'test'
        ];
        $app = new App($config);
        $app->run();
        self::assertTrue(true);
    }

    public function testRootPath()
    {
        $root_path = App::rootPath();
        self::assertEquals($root_path, dirname(__DIR__) . '/temp');
    }

    public function testRuntimePath()
    {
        $runtime_path = App::runtimePath();
        self::assertEquals($runtime_path, dirname(__DIR__) . '/temp/runtime');
    }

    public function testModule()
    {
        $module = App::module();
        self::assertEquals($module, 'test');
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
        self::assertEquals($action, 'test2');
    }

    public function testAppPath()
    {
        $app_path = App::appPath();
        self::assertEquals($app_path, dirname(__DIR__) . '/temp/app');
    }

    public function testConfigPath()
    {
        $config_path = App::configPath();
        self::assertEquals($config_path, dirname(__DIR__) . '/temp/config');
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
        self::assertEquals($controller, 'Index');
    }
}
