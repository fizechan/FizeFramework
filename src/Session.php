<?php


namespace fize\framework;

use fize\session\Session as FizeSession;


class Session extends FizeSession
{

    public function __construct(array $config = [])
    {
        parent::__construct($config);
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
}