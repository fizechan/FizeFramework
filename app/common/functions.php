<?php

use Fize\Framework\App;

/**
 * 当前应用
 * @return App
 */
function app(): App
{
    return App::getInstance();
}

function cache()
{

}

function config()
{

}

function cookie()
{

}

function env()
{

}

function db()
{

}

function lang()
{

}

function loger()
{

}

function session()
{

}

function url()
{

}

/**
 * 应用根目录路径
 * @return string
 */
function fize_runtime_path(): string
{
    return App::getInstance()->boot->rootPath();
}

/**
 * 应用视图目录路径
 * @return string
 */
function fize_view_path(): string
{
    $boot = App::getInstance()->boot;
    return $boot->appPath() . '/' . $boot->viewDir();
}
