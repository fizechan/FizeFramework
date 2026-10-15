<?php

namespace Tests;

use Fize\Framework\Config;
use PHPUnit\Framework\TestCase;

class TestConfig extends TestCase
{

    public function testGet()
    {
        $config = new Config(__DIR__ . '/../demo/config', 'index');
        $app = $config->get('app');
        var_dump($app);

        self::assertEquals('1.0', $app['version']);
    }
}
