<?php

namespace Tests;

use Fize\Framework\Boot;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TestBoot extends TestCase
{
    public function test__construct()
    {
        $root_path = dirname(__DIR__) . '/tests/fixtures';
        $boot = new Boot(['root_path' => $root_path]);

        self::assertIsArray($boot->get());
        self::assertEquals($root_path, $boot->get('root_path'));
        self::assertEquals($root_path, $boot->rootPath());
        self::assertEquals('app', $boot->appDir());
        self::assertEquals('config', $boot->configDir());
        self::assertEquals('runtime', $boot->runtimeDir());
        self::assertEquals('Controller', $boot->controllerDir());
        self::assertEquals('View', $boot->viewDir());
        self::assertEquals($root_path . '/app', $boot->appPath());
        self::assertEquals($root_path . '/config', $boot->configPath());
        self::assertEquals($root_path . '/runtime', $boot->runtimePath());
    }

    public function testRootPath()
    {
        $boot1 = new Boot(['root_path' => '/tmp/a', 'debug' => true]);
        self::assertEquals('/tmp/a', $boot1->rootPath());
        self::assertTrue($boot1->get('debug'));

        $boot2 = new Boot(['root_path' => '/tmp/b', 'debug' => false]);
        self::assertEquals('/tmp/b', $boot2->rootPath());
        self::assertFalse($boot2->get('debug'));
    }

    public function testBootPathRequired()
    {
        $this->expectException(InvalidArgumentException::class);
        new Boot([]);
    }
}
