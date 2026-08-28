<?php

namespace Tests;

use Fize\Framework\Config;
use Fize\Framework\Env;
use PHPUnit\Framework\TestCase;

class TestConfig extends TestCase
{
    /**
     * @var string
     */
    protected $dir;

    /**
     * @var array
     */
    protected $parameters;

    protected function setUp(): void
    {
        $root = dirname(__DIR__) . '/tests/fixtures';
        $env = new Env([
            'root_path' => $root,
            'app_dir'   => 'Fixture',
        ]);
        $this->dir = $env->configPath();
        $this->parameters = $env->parameters('Index');
    }

    public function testLayeredDeepMergeAndInterpolate()
    {
        $config = new Config($this->dir, $this->parameters, 'Index');
        $app = $config->get('app');

        self::assertEquals('1.0', $app['version']);
        self::assertEquals('app', $app['name']);
        self::assertEquals(1, $app['nested']['a']);
        self::assertEquals(20, $app['nested']['b']);
        self::assertEquals(30, $app['nested']['c']);
        self::assertEquals(1, $app['nested']['deep']['x']);
        self::assertEquals(2, $app['nested']['deep']['y']);
        self::assertEquals('Index', $app['module']);
        self::assertEquals(dirname(__DIR__) . '/tests/fixtures/runtime/app', $app['path']);
    }

    public function testSetModuleKeepsCachedFile()
    {
        $config = new Config($this->dir, $this->parameters, 'Index');
        self::assertEquals('Index', $config->get('app.module'));

        $config->setModule('Admin');
        self::assertEquals('Index', $config->get('app.module'));

        $url = $config->get('url');
        self::assertIsArray($url);
        self::assertArrayHasKey('rules', $url);
    }

    public function testInstancesAreIndependent()
    {
        $config1 = new Config($this->dir, $this->parameters, 'Index');
        $config2 = new Config($this->dir, $this->parameters, 'Admin');

        self::assertEquals('Index', $config1->get('app.module'));
        self::assertEquals('Admin', $config2->get('app.module'));
    }
}
