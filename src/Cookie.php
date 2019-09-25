<?php

namespace fize\framework;

use fize\security\OpenSSL;

/**
 * Cookie管理类
 */
class Cookie
{

    /**
     * 当前默认配置
     * 注意开启httponly后，前端JS是无法获取到cookie的，如果需要前端js获取cookie，可在设置cookie时禁用httponly
     * @var array
     */
    private static $config = [
        'expire'        => 3600, //cookie有效时间，以秒为单位
        'path'          => "/", //Cookie路径
        'domain'        => "", //Cookie有效域名
        'secure'        => false, //是否只允许在HTTPS安全链接下生效
        'httponly'      => true, //是否使用httponly，为安全性，全局默认开启
        'prefix'        => "", //Cookie键名前缀,如果发生冲突可以修改该值
        'encode_key'    => false, //是否加密cookie键名，加密键名则需要对所有cookie进行遍历获取，不合适cookie过多的情况
        'encode_value'  => false, //是否加密cookie键值
        'secret_key'    => "", //加密密钥
    ];

    /**
     * @var OpenSSL 开启加密时使用到的OpenSSL对象
     */
    protected static $openssl;

    /**
     * cookie被篡改时的事件回调函数收集器
     * @var array
     */
    private static $onTamperEvent = [];

    /**
     * 实例化
     * @param array $config 要更改的配置项
     */
    public function __construct(array $config)
    {
        self::$config = array_merge(self::$config, $config);
        
        if(self::$config['encode_key'] || self::$config['encode_value']) {
            self::$openssl = new OpenSSL();
            self::$openssl->setKey(self::$config['secret_key']);
        }
    }

    /**
     * 加密
     * @param string $value 待加密字符串
     * @return string
     */
    protected static function encode($value)
    {
        return self::$openssl->encrypt($value, 'aes-256-cbc');
    }

    /**
     * 解密
     * @param string $value 待解密字符串
     * @return string
     */
    protected static function decode($value)
    {
        return self::$openssl->decrypt($value, 'aes-256-cbc');
    }

    /**
     * 绑定cookie被篡改事件
     * @param callable $func cookie被篡改事件回调函数，支持参数$key, $value
     */
    public static function onTamper(callable $func)
    {
        self::$onTamperEvent[] = $func;
    }

    /**
     * 触发cookie被篡改事件
     * @param string $key 获取到的cookie键名(解密后)
     * @param string $value 获取到的cookie键值(无法解密的原加密字符串)
     */
    private static function fireTamperEvent($key, $value)
    {
        foreach (self::$onTamperEvent as $func) {
            $func($key, $value);
        }
    }

    /**
     * 设置一个cookie
     * @param string $key 键名
     * @param mixed $value 可以传递非标量值
     * @param mixed $config 本次临时指定的配置
     */
    public static function set($key, $value, array $config = [])
    {
        $config = array_merge(self::$config, $config);
        $key = $config['prefix'] . $key;
        if ($config['encode_key']) {
            $no_find = true;
            foreach ($_COOKIE as $k => $v) {
                if (self::decode($k) == $key) {
                    $key = $k;
                    $no_find = false;
                    break;
                }
            }
            if ($no_find) {
                $key = self::encode($key);
            }
        }
        $value = serialize($value);
        if ($config['encode_value']) {
            $value = self::encode($value);
        }
        setcookie($key, $value, time() + $config['expire'], $config['path'], $config['domain'], $config['secure'], $config['httponly']);
        $_COOKIE[$key] = $value; //使当前生效
    }

    /**
     * 获取指定cookie值，未设置则返回false
     * @param string $key cookie名(加密前)
     * @param array $config 附加和设置cookie时相同的配置才能获取到
     * @return mixed
     */
    public static function get($key, array $config = [])
    {
        $value = '';
        $config = array_merge(self::$config, $config);
        $key = $config['prefix'] . $key;
        if ($config['encode_key']) {
            $no_find = true;
            foreach ($_COOKIE as $k => $v) {
                if (self::decode($k) == $key) {
                    $value = $v;
                    $no_find = false;
                    break;
                }
            }
            if ($no_find) {
                return false;
            }
        } else {
            if (!isset($_COOKIE[$key])) {
                return false;
            }
            $value = $_COOKIE[$key];
        }
        if ($config['encode_value']) {
            $decode_str = self::decode($value);
            if ($decode_str === false) {
                self::fireTamperEvent($key, $value);
                return false;
            }
            $value = unserialize($decode_str);
        }
        return $value;
    }

    /**
     * 判断Cookie是否存在
     * @param string $key cookie名(加密前)
     * @param array $config 附加和设置cookie时相同的配置才能获取到
     * @return bool
     */
    public static function has($key, array $config = [])
    {
        return self::get($key, $config) !== false;
    }

    /**
     * 删除某个Cookie值
     * @param string $key cookie键名
     */
    public static function remove($key)
    {
        self::set($key, '', -3600);
        unset($_COOKIE[$key]);  //下文马上失效
    }

    /**
     * 清空Cookie值
     */
    public static function clear()
    {
        foreach ($_COOKIE as $key => $value) {
            setcookie($key, '', -3600);
            unset($_COOKIE[$key]);  //下文马上失效
        }
    }
}
