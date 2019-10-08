<?php

use PHPUnit\Framework\TestCase;
use fize\framework\Config;

class ConfigTest extends TestCase
{

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        new Config(__DIR__ . '/config', 'index');
    }

    public function testGet()
    {
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
    }
}
