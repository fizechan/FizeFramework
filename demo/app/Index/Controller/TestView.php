<?php


namespace App\Index\Controller;

use Fize\Framework\App;
use Fize\Framework\Controller\Common;
use Fize\View\View;


class TestView extends Common
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
