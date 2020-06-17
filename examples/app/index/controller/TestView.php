<?php


namespace app\index\controller;

use fize\framework\App;
use fize\framework\Controller;
use fize\view\View;


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
