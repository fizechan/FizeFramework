<?php


namespace app\index\controller;

use fize\web\Session;


class TestSession
{

    public function index()
    {
        $admin = [
            'name' => '陈峰展',
            'age' => 31
        ];
        Session::set('admin', $admin);

        $admin = Session::get('admin');
        var_dump($admin);
    }
}
