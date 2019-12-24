<?php


namespace app\index\controller;

use fize\framework\Controller;
use fize\framework\App;


class Test extends Controller
{
    public function __construct()
    {
        $_REQUEST['_ajax'] = 1;  //伪装AJAX
        if(App::action() == 'tvalidate') {
            $this->validate();
        }
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
        echo '这里执行不到咯';
    }

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

    public function tredirect()
    {
        $this->redirect('https://www.baidu.com');
        echo '这里执行不到咯';
    }

    public function tvalidate()
    {
        echo '本例用于测试验证器';
    }
}