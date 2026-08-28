<?php

namespace Tests;

use Fixture\Index\Controller\Index;
use Fize\Framework\App;
use PHPUnit\Framework\TestCase;

class TestController extends TestCase
{
    /**
     * @var int
     */
    protected $obLevel;

    protected function setUp(): void
    {
        $this->obLevel = ob_get_level();
        $_GET = [];
    }

    protected function tearDown(): void
    {
        while (ob_get_level() > $this->obLevel) {
            ob_end_clean();
        }
        restore_error_handler();
        restore_exception_handler();
    }

    public function testBindAppAndConfigAccess()
    {
        $app = new App([
            'root_path' => dirname(__DIR__) . '/tests/fixtures',
            'app_dir'   => 'Fixture',
            'module'    => 'Index',
            'debug'     => false,
        ]);

        $controller = $app->container()->make(Index::class);
        $controller->bindApp($app);

        self::assertSame($app, App::getInstance());
        self::assertEquals('1.0', $controller->version());
    }
}
