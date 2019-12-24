<?php


namespace app\index\controller;

use fize\cache\Cache;


class Tcache
{

    public function set()
    {
        Cache::set('name', '陈峰展');
        var_dump(Cache::get('name'));
    }
}