<?php

namespace Fize\Framework\Facade;

use Fize\Framework\App;

/**
 * 环境变量
 */
class Env
{

    /**
     * 获取环境变量值
     * @param string|null $name    环境变量名
     * @param mixed       $default 默认值
     * @return mixed
     */
    public static function get(?string $name = null, $default = null)
    {
        $app = App::getInstance();
        return $app->env->get($name, $default);
    }

}