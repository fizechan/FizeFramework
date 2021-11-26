<?php

namespace app\admin\controller;

use Fize\Framework\Url;

class Test2
{

    public function index()
    {
        var_dump($_GET);
        $url = Url::create('/admin/Test2/test', ['name' => 'cfz']);
        var_dump($url);
        echo 'admin/Test2/index';
    }

    public function test()
    {
        var_dump($_GET);
        echo 'admin/Test2/test';
    }

    /**
     * 本例用于测试关键字作为方法名的可用性
     */
    public function echo()
    {
        var_dump($_GET);
        echo 'admin/Test2/echo';
    }
}
