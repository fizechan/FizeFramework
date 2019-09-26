<?php


namespace app\index\controller;

use fize\framework\Controller;
use fize\framework\Config;
use fize\framework\View;
use fize\framework\Response;


class Index extends Controller
{

    public function index()
    {
        echo 'Hello FizeFramework';
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