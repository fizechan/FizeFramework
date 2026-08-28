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
     * @var array 占位符映射
     */
    protected $parameters = [];

    /**
     * @var array 已读取到的配置
     */
    protected $config = [];

    /**
     * 初始化
     * @param string      $dir        配置目录
     * @param array       $parameters 占位符映射
     * @param string|null $module     指定要附加配置的模块名
     */
    public function __construct(string $dir, array $parameters = [], string $module = null)
    {
        $this->dir = $dir;
        $this->parameters = $parameters;
        $this->module = $module;
        $this->syncModuleParameters();
    }

    /**
     * 切换模块（不清已缓存配置）
     * @param string|null $module 模块名
     */
    public function setModule(string $module = null): void
    {
        $this->module = $module;
        $this->syncModuleParameters();
    }

    /**
     * 从配置数组中获取配置
     * @param array  $config 配置数组
     * @param string $key    键名，层级以.分隔
     * @return mixed
     */
    protected function getByKey(array $config, string $key)
    {
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
     * @param string $key     键名，层级以.分隔
     * @param mixed  $default 如未找到该配置时返回的默认值
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $value = $this->getByKey($this->config, $key);
        if (!is_null($value)) {
            return $value;
        }

        $keys = explode('.', $key);
        $file_name = $keys[0] . '.php';

        $cfg_files = [];
        $cfg_files[] = __DIR__ . '/../app/config/' . $file_name;
        $cfg_files[] = $this->dir . '/' . $file_name;
        $cfg_files[] = $this->dir . '/common/' . $file_name;
        if ($this->module) {
            $cfg_files[] = $this->dir . '/' . $this->module . '/' . $file_name;
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

        $this->config[$keys[0]] = $this->interpolate($config);

        $value = $this->getByKey($this->config, $key);
        if (is_null($value)) {
            return $default;
        }

        return $value;
    }

    /**
     * 根据当前模块更新插值参数
     */
    protected function syncModuleParameters(): void
    {
        $module_name = $this->module ?: '';
        $this->parameters['%module%'] = $module_name;
        $app_path = $this->parameters['%app_path%'] ?? '';
        $this->parameters['%module_path%'] = $module_name !== '' ? $app_path . '/' . $module_name : $app_path;
    }

    /**
     * 替换配置值中的占位符
     * @param array $config 配置
     * @return array
     */
    protected function interpolate(array $config): array
    {
        if (!$this->parameters) {
            return $config;
        }
        array_walk_recursive($config, function (&$value) {
            if (is_string($value)) {
                $value = strtr($value, $this->parameters);
            }
        });
        return $config;
    }
}
