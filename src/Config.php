<?php
/** @noinspection PhpIncludeInspection */


namespace fize\framework;

use fize\io\File;

/**
 * 配置类
 * @todo 使用数组递归合并读取不易于维护，且容易产生异议，考虑使用简单替换
 * @package fize\framework
 */
class Config
{
    /**
     * @var string 配置目录
     */
    protected static $dir;

    /**
     * @var string 当前模块
     */
    protected static $module;

    /**
     * @var array 已读取到的配置
     */
    protected static $config = [];

    /**
     * 初始化
     * @param string $dir 配置目录
     * @param string $module 指定要附加配置的模块名
     */
    public function __construct($dir, $module = null)
    {
        self::$dir = $dir;
        self::$module = $module;
    }

    /**
     * 从配置数组中获取配置
     * @param array $config 配置数组
     * @param string $key 键名，层级以.分隔
     * @return mixed
     */
    protected static function getByKey(array $config, $key)
    {
        $keys = explode('.', $key);
        $cfg_temp = $config;
        foreach ($keys as $key) {
            if (isset($cfg_temp[$key])) {
                $cfg_temp = $cfg_temp[$key];
            } else {
                return null;
            }
        }
        return $cfg_temp;
    }

    /**
     * 数组递归合并,覆盖方式
     * @param array $arr1 数组1
     * @param array $arr2 数组2
     * @return array
     */
    protected static function arrayCoverRecursive($arr1, $arr2)
    {
        $rs = $arr1;

        foreach ($arr2 as $key => $val) {
            $rs[$key] = isset($rs[$key]) ? $rs[$key] : [];
            $rs[$key] = is_array($val) ? self::arrayCoverRecursive($rs[$key], $val) : $val;
        }
        return $rs;
    }

    /**
     * 获取配置
     * @param string $key 键名，层级以.分隔
     * @param mixed $default 如未找到该配置时返回的默认值
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        //当前缓存配置
        $value = self::getByKey(self::$config, $key);
        if (!is_null($value)) {
            return $value;
        }

        $keys = explode('.', $key);
        $file_name = $keys[0] . '.php';

        //框架默认配置
        $cfg_files[] = __DIR__ . '/config/' . $file_name;
        //应用默认配置
        $cfg_files[] = self::$dir . '/' . $file_name;
        //公共模块配置
        $cfg_files[] = self::$dir . '/common/' . $file_name;
        //当前模块配置
        if (self::$module) {
            $cfg_files[] = self::$dir . '/' . self::$module . '/' . $file_name;
        }

        $config = [];
        foreach ($cfg_files as $cfg_file) {
            if (File::exists($cfg_file)) {
                $append = include_once $cfg_file;
                //$config = array_merge($config, $append);
                $config = self::arrayCoverRecursive($config, $append);
            }
        }

        self::$config[$keys[0]] = $config;

        $value = self::getByKey(self::$config, $key);
        if (!is_null($value)) {
            return $value;
        }

        return $default;
    }
}