<?php


use PHPUnit\Framework\TestCase;
use fize\framework\App;


class ControllerTest extends TestCase
{
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

        new App($config);
    }

    public function testResult()
    {
        $_GET['_r'] = 'test/tresult';  //伪装路由
        $config = [
            'root_path' => dirname(__DIR__) . '/temp',
            'module'    => 'test'
        ];
        $app = new App($config);
        $app->run();
        self::assertTrue(true);
    }
}
