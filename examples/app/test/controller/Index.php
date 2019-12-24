<?php


namespace app\test\controller;

use fize\framework\Controller;
use fize\framework\Config;
use fize\view\View;
use fize\web\Response;


class Index extends Controller
{

    public function index()
    {
        echo 'Hello FizeFramework';
    }

    public function test()
    {
        echo 'Hello FizeFramework1';
    }

    public function test2()
    {
        echo 'Hello FizeFramework2';
    }

    public function config()
    {
        $config = Config::get('app');
        var_dump($config);
    }

    public function view()
    {
        View::assign('fize', ['name' => '陈峰展', 'mobile' => '14759786559']);

        $rows = [
            ['id' => 0, 'name' => '陈峰展1'],
            ['id' => 1, 'name' => '陈峰展2'],
            ['id' => 2, 'name' => '陈峰展3'],
            ['id' => 3, 'name' => '陈峰展4'],
        ];
        View::assign('rows', $rows);
        //return View::render();  //字符串
        return Response::html(View::render());
    }

    public function json()
    {
        $rows = [
            ['id' => 0, 'name' => '陈峰展1'],
            ['id' => 1, 'name' => '陈峰展2'],
            ['id' => 2, 'name' => '陈峰展3'],
            ['id' => 3, 'name' => '陈峰展4'],
        ];
        return Response::json($rows);
    }
}