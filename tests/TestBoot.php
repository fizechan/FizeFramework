<?php

namespace Tests;

use Fize\Framework\Boot;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TestBoot extends TestCase
{
    public function testGetAndPaths()
    {
        $Boot = dirname(__DIR__) . '/tests/fixtures';
        $boot = new Boot(['Boot_path' => $Boot]);

        self::assertIsArray($boot->get());
        self::assertEquals($Boot, $boot->get('Boot_path'));
        self::assertEquals($Boot, $boot->BootPath());
        self::assertEquals('app', $boot->appDir());
        self::assertEquals('config', $boot->configDir());
        self::assertEquals('runtime', $boot->runtimeDir());
        self::assertEquals('Controller', $boot->controllerDir());
        self::assertEquals('View', $boot->viewDir());
        self::assertEquals($Boot . '/app', $boot->appPath());
        self::assertEquals($Boot . '/config', $boot->configPath());
        self::assertEquals($Boot . '/runtime', $boot->runtimePath());
    }

    public function testInstancesAreIndependent()
    {
        $boot1 = new Boot(['Boot_path' => '/tmp/a', 'debug' => true]);
        $boot2 = new Boot(['Boot_path' => '/tmp/b', 'debug' => false]);

        self::assertEquals('/tmp/a', $boot1->rootPath());
        self::assertEquals('/tmp/b', $boot2-rootPath());
        self::assertTrue($boot1->get('debug'));
        self::assertFalse($boot2->get('debug'));
    }

    public function testBootPathRequired()
    {
        $this->expectException(InvalidArgumentException::class);
        new Boot([]);
    }

    public function testParameters()
    {
        $boot = new Boot(['Boot_path' => '/proj']);
        $params = $boot->parameters();
        self::assertEquals('/proj', $params['%Boot_path%']);
        self::assertEquals('/proj/app', $params['%module_path%']);
        self::assertEquals('', $params['%module%']);

        $with_module = $boot->parameters('Index');
        self::assertEquals('Index', $with_module['%module%']);
        self::assertEquals('/proj/app/Index', $with_module['%module_path%']);
    }
}
