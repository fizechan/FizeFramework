<?php


namespace fize\framework;

/**
 * URL管理类，包括路由解析，URL生成等功能
 * @package fize\framework
 */
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

    public static function parse($url)
    {

    }

    public static function create($url, array $params = [])
    {

    }
}