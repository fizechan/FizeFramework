<?php

namespace App\Index\Controller;

use Fize\Framework\Controller\Common;

class News extends Common
{

    public function details($id)
    {
        echo "模拟读取新闻\r\n<br/>";
        echo "本例用于测试伪静态定义\r\n<br/>";
        echo $id;
    }
}
