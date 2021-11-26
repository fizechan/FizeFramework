<?php


namespace app\index\controller;

use Fize\Cache\Cache;


class TestCache
{

    public function set()
    {
        Cache::set('name', '陈峰展');
        var_dump(Cache::get('name'));
    }
}
