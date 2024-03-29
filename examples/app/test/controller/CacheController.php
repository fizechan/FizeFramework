<?php


namespace app\test\controller;

use Fize\Cache\Cache;


class CacheController
{

    public function get()
    {
        $cache1 = Cache::get('cache1');
        var_dump($cache1);

        $cache2 = Cache::get('cache2');
        var_dump($cache2);
    }

    public function set()
    {
        Cache::delete('cache2');
        $cache2 = Cache::get('cache2');
        var_dump($cache2);
        Cache::set('cache2', 'value2');
        $cache2 = Cache::get('cache2');
        var_dump($cache2);
    }

    public function has()
    {
        Cache::delete('cache2');
        $has2 = Cache::has('cache2');
        var_dump($has2);

        Cache::set('cache2', 'value2');
        var_dump($has2);
    }

    public function clear()
    {
        $has2 = Cache::has('cache2');
        var_dump($has2);

        Cache::clear();

        $has2 = Cache::has('cache2');
        var_dump($has2);
    }

    public function testRemove()
    {
        Cache::set('cache2', 'value2');
        $has2 = Cache::has('cache2');
        var_dump($has2);

        Cache::delete('cache2');

        $has2 = Cache::has('cache2');
        var_dump($has2);
    }
}