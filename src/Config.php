<?php

namespace Fize\Framework;

use Fize\IO\File;

/**
 * 配置
 */
class Config
{

    /**
     * @var string 配置目录
     */
    protected $dir;

    /**
     * @var string|null 当前模块
     */
    protected $module;

    /**
     * @var array 已读取到的配置
     */
    protected $config = [];

    /**
     * 初始化
     * @param string      $dir    配置目录
     * @param string|null $module 指定要附加配置的模块名
     */
    public function __construct(string $dir, string $module = null)
    {
        $this->dir = $dir;
        $this->module = $module;
    }

    /**
     * 设置模块
     * @param string|null $module 模块名
     */
    public function setModule(string $module = null): void
    {
        $this->module = $module;
    }

    /**
     * 从配置数组中获取配置
     * @param array       $config 配置数组
     * @param string|null $key    键名，层级以.分隔
     * @return mixed
     */
    protected static function getByKey(array $config, string $key = null)
    {
        if ($config && is_null($key)) {
            return $config;
        }

        $keys = explode('.', $key);
        $cfg_temp = $config;
        foreach ($keys as $key_item) {
            if (isset($cfg_temp[$key_item])) {
                $cfg_temp = $cfg_temp[$key_item];
            } else {
                return null;
            }
        }
        return $cfg_temp;
    }

    /**
     * 获取配置
     * @param string      $name    配置名
     * @param string|null $key     键名，层级以.分隔
     * @param mixed       $default 如未找到该配置时返回的默认值
     * @return mixed
     */
    public function get(string $name, string $key = null, $default = null)
    {
        $value = self::getByKey($this->config, $key);
        if (!is_null($value)) {
            return $value;
        }

        $file_name = $name . '.php';
        $cfg_files = [];
        $cfg_files[] = __DIR__ . '/../app/config/' . $file_name;  // 框架配置
        $cfg_files[] = $this->dir . '/' . $file_name;             // 主配置
        $cfg_files[] = $this->dir . '/common/' . $file_name;      // 公共模块配置覆盖
        if ($this->module) {
            $cfg_files[] = $this->dir . '/' . $this->module . '/' . $file_name;  // 应用模块配置覆盖
        }

        $config = [];
        foreach ($cfg_files as $cfg_file) {
            if (File::exists($cfg_file)) {
                $append = require $cfg_file;
                if (is_array($append)) {
                    $config = array_replace_recursive($config, $append);
                }
            }
        }

        $this->config = $config;
        $value = self::getByKey($config, $key);
        if (is_null($value)) {
            return $default;
        }
        return $value;
    }
}
