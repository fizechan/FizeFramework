<?php


namespace app\test\controller;

use Exception;
use fize\framework\Controller;


class Test extends Controller
{

    public function tresult()
    {
        $_REQUEST['_ajax'] = 1;  //伪装AJAX
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
        $_REQUEST['_ajax'] = 1;  //伪装AJAX
        $this->success('测试控制器内置方法success');
    }

    public function terror()
    {
        $_REQUEST['_ajax'] = 1;  //伪装AJAX
        $this->error('测试控制器内置方法error');
    }

    public function tredirect()
    {
        $this->redirect('https://www.baidu.com');
    }

    public function terror_handler()
    {
        $kk = 100 / 0;
        var_dump($kk);
    }

    public function texception_handler()
    {
        throw new Exception('测试错误', 110);
    }
}