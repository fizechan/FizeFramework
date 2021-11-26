<?php


namespace app\index\controller;

use Fize\Framework\App;
use Fize\Framework\Controller;
use Fize\View\View;


class TestView extends Controller
{

    public function index()
    {
        var_dump(App::controller());
        var_dump(App::action());
    }

    public function view()
    {
        return View::render();
    }
}
