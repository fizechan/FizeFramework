<?php

namespace app\index\controller;

use RuntimeException;
use Fize\Database\Db;
use Fize\Framework\Config;
use Fize\Framework\Controller;
use Fize\View\View;


class Index extends Controller
{

    public function index()
    {
        $version = Config::get('app.version');
        View::assign('version', $version);

        $rows = Db::table('user')->limit(10)->select();
        View::assign('users', $rows);

        View::assign('title', 'Hello FizeFramework.');
        View::assign('body', 'This is a Demo for FizeFramework.');

        return View::render();
    }

    public function test_error()
    {
        $num = 1 / 0;
        var_dump($num);
    }

    public function test_exception()
    {
        throw new RuntimeException('测试异常1', 500);
    }
}
