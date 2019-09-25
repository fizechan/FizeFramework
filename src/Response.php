<?php

namespace fize\framework;


/**
 * 响应类
 */
class Response
{

    /**
     * 强制浏览器不进行缓存
     */
    public static function noCache()
    {
        header("Cache-Control: no-cache");
        header("Pragma: no-cache");
    }

    public static function json()
    {

    }

    public static function view()
    {

    }

    public static function html()
    {

    }

    /**
     * 跳转
     */
    public static function redirect()
    {

    }
}