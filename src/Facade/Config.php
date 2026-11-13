<?php

namespace Fize\Framework\Facade;

use Fize\Framework\App;

/**
 * 配置
 */
class Config
{
    /**
     * 获取配置
     * @param string      $name    配置名
     * @param string|null $key     键名，层级以.分隔
     * @param mixed       $default 如未找到该配置时返回的默认值
     * @return mixed
     */
    public static function get(string $name, ?string $key = null, $default = null)
    {
        $app = App::getInstance();
        return $app->config->get($name, $key, $default);
    }
}