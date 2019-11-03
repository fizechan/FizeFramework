<?php


namespace app\test\controller;

use fize\framework\Controller;


class Test extends Controller
{

    public function __construct()
    {
        $_REQUEST['_ajax'] = 1;  //伪装AJAX
    }

    public function tresult()
    {
        $data = [
            'name' => '陈峰展',
            'age'  => 31,
            'sex'  => true
        ];
        $message = '测试控制器内置方法result';
        $code = 10086;
        $this->result($data, $message, $code);
    }

    public function tsuccess()
    {
        $this->success('测试控制器内置方法success');
    }

    public function terror()
    {
        $this->error('测试控制器内置方法error');
    }

    public function tredirect()
    {
        $this->redirect('https://www.baidu.com');
    }
}