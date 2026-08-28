<?php

namespace Tests;

use Fize\Framework\App;
use Fize\Framework\Config;
use Fize\Framework\Env;
use Fize\Framework\Url;
use PHPUnit\Framework\TestCase;

class TestApp extends TestCase
{
    /**
     * @var int
     */
    protected $obLevel;

    protected function setUp(): void
    {
        $this->obLevel = ob_get_level();
        $_GET = [];
        $_SERVER['PATH_INFO'] = '';
    }

    protected function tearDown(): void
    {
        while (ob_get_level() > $this->obLevel) {
            ob_end_clean();
        }
        restore_error_handler();
        restore_exception_handler();
    }

    /**
     * @return App
     */
    protected function createApp(array $env = []): App
    {
        return new App(array_merge([
            'root_path' => dirname(__DIR__) . '/tests/fixtures',
            'app_dir'   => 'Fixture',
            'module'    => 'Index',
            'debug'     => false,
        ], $env));
    }

    public function testGetInstanceAndServices()
    {
        $app = $this->createApp();

        self::assertSame($app, App::getInstance());
        self::assertSame($app->env, $app->container()->get(Env::class));
        self::assertSame($app->config, $app->container()->get(Config::class));
        self::assertSame($app->url, $app->container()->get(Url::class));
        self::assertEquals('Index', App::module());
        self::assertEquals('Index', App::controller());
        self::assertEquals('index', App::action());
    }

    public function testActionFromRoute()
    {
        $_GET['_r'] = '/index/test';
        $this->createApp();

        self::assertEquals('Index', App::controller());
        self::assertEquals('test', App::action());
    }
}
