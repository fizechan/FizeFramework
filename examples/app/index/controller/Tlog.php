<?php


namespace app\index\controller;

use fize\log\Log;


class Tlog
{
    public function index()
    {
        Log::info('这是个提示信息');
        Log::error('发生错误啦!');
        echo 'OK';
    }
}