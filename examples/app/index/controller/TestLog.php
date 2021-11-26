<?php


namespace app\index\controller;

use Fize\Log\Log;


class TestLog
{
    public function index()
    {
        Log::info('这是个提示信息');
        Log::error('发生错误啦!');
        echo 'OK';
    }
}
