<?php

namespace Fixture\Index\Controller;

use Fize\Framework\Controller;

class Index extends Controller
{
    public function index()
    {
        return 'ok';
    }

    public function test()
    {
        return 'test';
    }

    public function version()
    {
        return $this->app->config->get('app.version');
    }
}
