<?php


namespace fize\framework;


class Url
{

    /**
     * @var string 路由GET参数名
     */
    protected static $routeKey;

    public function __construct($route_key)
    {
        self::$routeKey = $route_key;
    }

    public static function route()
    {

    }

    public static function rule()
    {

    }

    public static function build()
    {

    }

    public static function url()
    {

    }

    public static function to()
    {

    }
}