<?php

use Fize\Cache\Cache;
use Fize\Database\Core\Db as CoreDb;
use Fize\Framework\App;
use Fize\Framework\Facade\Config;
use Fize\Framework\Facade\Db;
use Fize\Framework\Facade\Env;

/**
 * 当前应用
 * @return App
 * @deprecated 不建议使用！
 */
function app(): App
{
    return App::getInstance();
}

function cache()
{

}

/**
 * 获取配置
 * @param string      $name    配置名
 * @param string|null $key     键名，层级以.分隔
 * @param mixed       $default 如未找到该配置时返回的默认值
 * @return mixed
 */
function config(string $name, ?string $key = null, $default = null)
{
    return Config::get($name, $key, $default);
}

function cookie()
{

}

/**
 * 指定当前要操作的表
 *
 * 支持链式调用
 * @param string      $name   表名
 * @param string|null $prefix 表前缀，默认为null表示使用当前前缀
 * @return CoreDb
 */
function db(string $name, ?string $prefix = null): CoreDb
{
    return Db::table($name, $prefix);
}

/**
 * 获取环境变量值
 * @param string|null $name    环境变量名
 * @param mixed       $default 默认值
 * @return mixed
 */
function env(?string $name = null, $default = null)
{
    return Env::get($name, $default);
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

function view()
{
    
}
