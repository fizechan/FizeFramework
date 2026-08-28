<?php

namespace Tests;

use Fize\Framework\Env;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TestEnv extends TestCase
{
    public function testGetAndPaths()
    {
        $root = dirname(__DIR__) . '/tests/fixtures';
        $env = new Env(['root_path' => $root]);

        self::assertIsArray($env->get());
        self::assertEquals($root, $env->get('root_path'));
        self::assertEquals($root, $env->rootPath());
        self::assertEquals('app', $env->appDir());
        self::assertEquals('config', $env->configDir());
        self::assertEquals('runtime', $env->runtimeDir());
        self::assertEquals('Controller', $env->appControllerDir());
        self::assertEquals('View', $env->appViewDir());
        self::assertEquals($root . '/app', $env->appPath());
        self::assertEquals($root . '/config', $env->configPath());
        self::assertEquals($root . '/runtime', $env->runtimePath());
    }

    public function testInstancesAreIndependent()
    {
        $env1 = new Env(['root_path' => '/tmp/a', 'debug' => true]);
        $env2 = new Env(['root_path' => '/tmp/b', 'debug' => false]);

        self::assertEquals('/tmp/a', $env1->rootPath());
        self::assertEquals('/tmp/b', $env2->rootPath());
        self::assertTrue($env1->get('debug'));
        self::assertFalse($env2->get('debug'));
    }

    public function testRootPathRequired()
    {
        $this->expectException(InvalidArgumentException::class);
        new Env([]);
    }

    public function testParameters()
    {
        $env = new Env(['root_path' => '/proj']);
        $params = $env->parameters();
        self::assertEquals('/proj', $params['%root_path%']);
        self::assertEquals('/proj/app', $params['%module_path%']);
        self::assertEquals('', $params['%module%']);

        $with_module = $env->parameters('Index');
        self::assertEquals('Index', $with_module['%module%']);
        self::assertEquals('/proj/app/Index', $with_module['%module_path%']);
    }
}
