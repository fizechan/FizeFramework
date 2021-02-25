<?php


use fize\framework\Env;
use PHPUnit\Framework\TestCase;

class TestEnv extends TestCase
{

    public function testRuntimeDir()
    {

    }

    public function testGet()
    {
        $env = Env::get();
        var_dump($env);
        self::assertIsArray($env);

        $root_path = Env::get('root_path');
        var_dump($root_path);
        self::assertEquals($root_path, dirname(__DIR__) . '/temp');
    }

    public function testAppPath()
    {
        $app_path = Env::appPath();
        self::assertEquals($app_path, dirname(__DIR__) . '/temp/app');
    }

    public function testConfigPath()
    {
        $config_path = Env::configPath();
        self::assertEquals($config_path, dirname(__DIR__) . '/temp/config');
    }

    public function testAppDir()
    {

    }

    public function testAppControllerDir()
    {

    }

    public function testAppViewDir()
    {

    }

    public function testConfigDir()
    {

    }

    public function test__construct()
    {

    }

    public function testRootPath()
    {
        $root_path = Env::rootPath();
        self::assertEquals($root_path, dirname(__DIR__) . '/temp');
    }

    public function testRuntimePath()
    {
        $runtime_path = Env::runtimePath();
        self::assertEquals($runtime_path, dirname(__DIR__) . '/temp/runtime');
    }
}
