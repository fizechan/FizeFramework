<?php


namespace fize\framework;

use fize\cache\Cache as FizeCache;
use fize\cache\CacheHandler;

/**
 * 缓存类
 * @package fize\framework
 */
class Cache
{

    /**
     * @var CacheHandler
     */
    protected static $cache;

    /**
     * 在构造方法中设置静态属性
     * @param array $config 配置项
     */
    public function __construct(array $config)
    {
        self::$cache = FizeCache::getInstance($config['handler'], $config['config']);
    }

    /**
     * 获取一个缓存
     * @param string $name 缓存名
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function get($name, $default = null)
    {
        return self::$cache->get($name, $default);
    }

    /**
     * 查看指定缓存是否存在
     * @param string $name 缓存名
     * @return bool
     */
    public static function has($name)
    {
        return self::$cache->has($name);
    }

    /**
     * 设置一个缓存
     * @param string $name 缓存名
     * @param mixed $value 缓存值
     * @param int $expire 有效时间，以秒为单位,0表示永久有效
     */
    public static function set($name, $value, $expire = null)
    {
        self::$cache->set($name, $value, $expire);
    }

    /**
     * 删除一个缓存
     * @param string $name 缓存名
     */
    public static function remove($name)
    {
        self::$cache->remove($name);
    }

    /**
     * 清空缓存
     */
    public static function clear()
    {
        self::$cache->clear();
    }
}