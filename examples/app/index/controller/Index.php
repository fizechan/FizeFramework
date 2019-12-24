<?php


namespace app\index\controller;

use fize\framework\Controller;
use fize\framework\Config;
use fize\db\Db;
use fize\view\View;


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
}