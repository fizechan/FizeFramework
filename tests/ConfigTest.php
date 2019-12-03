<?php

use PHPUnit\Framework\TestCase;
use fize\framework\Config;

class ConfigTest extends TestCase
{

    public function testGet()
    {
        new Config(dirname(__DIR__) . '/temp/config', 'index');

        $cfg_app = Config::get('app');
        var_dump($cfg_app);
        self::assertIsArray($cfg_app);
        self::assertEquals($cfg_app['test0'], '0');
        self::assertEquals($cfg_app['test1'], '1(1)');
        self::assertIsArray($cfg_app['test2']);
        self::assertEquals(count($cfg_app['test2']), 4);
        self::assertEquals($cfg_app['test2']['test21'], '2-21(1)');
        self::assertIsArray($cfg_app['test2']['test22']);
        self::assertEquals($cfg_app['test2']['test22']['test221'], '2-221');
        self::assertEquals($cfg_app['test2']['test22']['test222'], '2-222(1)');
        self::assertEquals($cfg_app['test2']['test22']['test223'], '2-223(2)');
        self::assertEquals($cfg_app['test2']['test23'], '2-21(2)');
        self::assertEquals($cfg_app['test2']['test24'], '2-24(2)');

        new Config(dirname(__DIR__) . '/temp/config', 'admin');

        $cfg_app = Config::get('app');
        var_dump($cfg_app);

        $cfg_url = Config::get('url');
        var_dump($cfg_url);
    }
}
