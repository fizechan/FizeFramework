<?php


namespace app\index\controller\sub;

use Fize\Framework\Controller;
use Fize\View\View;


class TestView extends Controller
{

    public function view()
    {
        return View::render();
    }
}
