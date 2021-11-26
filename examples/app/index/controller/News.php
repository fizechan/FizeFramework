<?php


namespace app\index\controller;

use Fize\Framework\Controller;

class News extends Controller
{

    public function details($id)
    {
        echo "模拟读取新闻\r\n<br/>";
        echo "本例用于测试伪静态定义\r\n<br/>";
        echo $id;
    }
}
