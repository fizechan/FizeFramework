<?php


namespace app\index\controller\sub;

use fize\framework\Controller;
use fize\view\View;


class TestView extends Controller
{

    public function view()
    {
        return View::render();
    }
}
