<?php

namespace Tests;

use Fize\Framework\Config;
use PHPUnit\Framework\TestCase;

class TestConfig extends TestCase
{

    public function testGet()
    {
        new Config(dirname(__DIR__) . '/examples/config', 'index');

        $cfg_app = Config::get('app');
        var_dump($cfg_app);
        self::assertIsArray($cfg_app);
        self::assertEquals('0', $cfg_app['test0']);
        self::assertEquals('1(1)', $cfg_app['test1']);
        self::assertIsArray($cfg_app['test2']);
        self::assertCount(3, $cfg_app['test2']);
        self::assertEquals('2-21(2)', $cfg_app['test2']['test23']);
        self::assertIsArray($cfg_app['test2']['test22']);
        self::assertEquals('2-24(2)', $cfg_app['test2']['test24']);

        new Config(dirname(__DIR__) . '/temp/config', 'admin');

        $cfg_app = Config::get('app');
        var_dump($cfg_app);

        $cfg_url = Config::get('url');
        var_dump($cfg_url);
    }
}
