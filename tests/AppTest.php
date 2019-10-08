<?php
/** @noinspection PhpIncludeInspection */


use fize\framework\App;
use PHPUnit\Framework\TestCase;

class AppTest extends TestCase
{

    /**
     * @var App
     */
    protected $app;

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
        $this->app = new App($config);
    }

    public function testEnv()
    {
        $env = App::env();
        var_dump($env);
        self::assertIsArray($env);

        $root_path = App::env('root_path');
        var_dump($root_path);
        self::assertEquals($root_path, __DIR__);
    }

    public function testRun()
    {
        $_GET['_r'] = 'index/test';  //伪装路由
        $this->app->run();

        self::assertTrue(true);
    }

    public function testRootPath()
    {
        $root_path = App::rootPath();
        self::assertEquals($root_path, __DIR__);
    }

    public function testRuntimePath()
    {
        $runtime_path = App::runtimePath();
        self::assertEquals($runtime_path, __DIR__ . '/runtime');
    }

    public function testModule()
    {
        $module = App::module();
        self::assertEquals($module, 'test');
    }

    public function testAction()
    {
        $_GET['_r'] = 'index/test2';  //伪装路由
        $this->app->run();  //执行
        $action = App::action();
        self::assertEquals($action, 'test2');
    }

    public function testAppPath()
    {
        $app_path = App::appPath();
        self::assertEquals($app_path, __DIR__ . '/app');
    }

    public function testConfigPath()
    {
        $config_path = App::configPath();
        self::assertEquals($config_path, __DIR__ . '/config');
    }

    public function testController()
    {
        $_GET['_r'] = 'index/test2';  //伪装路由
        $this->app->run();  //执行
        $controller = App::controller();
        self::assertEquals($controller, 'Index');
    }
}
