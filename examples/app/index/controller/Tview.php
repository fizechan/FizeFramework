<?php


namespace app\index\controller;

use fize\framework\Controller;


class Tview extends Controller
{

    public function tsuccess()
    {
        unset($_REQUEST['_ajax']);
        $this->success('测试控制器内置方法success');
        echo '这里执行不到咯';
    }

    public function terror()
    {
        unset($_REQUEST['_ajax']);
        $this->error('测试控制器内置方法error');
        echo '这里执行不到咯';
    }

    public function tterr()
    {
        $rst = 100 / 0;  // 除 0 错误
        var_dump($rst);
    }
}