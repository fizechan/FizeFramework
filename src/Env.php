<?php


namespace fize\framework;

/**
 * 环境
 */
class Env
{

    /**
     * @var array 环境配置
     */
    protected static $env;

    /**
     * 构造
     * @param array $env 环境配置
     */
    public function __construct(array $env = [])
    {
        $this->init($env);
    }

    /**
     * 环境初始化
     * @param array $env 参数
     */
    protected function init(array $env)
    {
        $default_env = [
            'root_path'          => null,  // 根目录
            'app_dir'            => 'app',  // 应用文件夹
            'config_dir'         => 'config',  // 配置文件夹
            'runtime_dir'        => 'runtime',  // 运行时文件夹
            'app_controller_dir' => 'controller',  // 控制器文件夹
            'app_view_dir'       => 'view',  // 视图文件夹
            'module'             => true,  // true表示开启分组并自动判断，false表示关闭分组，字符串表示指定分组
            'default_module'     => 'index',  // 开启分组时的默认分组
            'route_key'          => '_r',  // 兼容模式路由GET参数名
            'debug'              => false,  // 是否调试模式
        ];
        $env = array_merge($default_env, $env);

        if (is_null($env['root_path'])) {
            $root_path = dirname(__FILE__, 5);  // 使用composer放置在vendor文件夹中的相对位置
            $env['root_path'] = $root_path;
        }

        self::$env = $env;
    }

    /**
     * 获取底层框架配置
     * @param string|null $key 如果指定该值，则返回该值指定的配置
     * @return mixed
     */
    public static function get(string $key = null)
    {
        if ($key) {
            return self::$env[$key];
        }
        return self::$env;
    }

    /**
     * 获取根目录路径
     * @return string
     */
    public static function rootPath(): string
    {
        return self::$env['root_path'];
    }

    /**
     * 获取应用文件夹名称
     * @return string
     */
    public static function appDir(): string
    {
        return self::$env['app_dir'];
    }

    /**
     * 获取配置文件夹名称
     * @return string
     */
    public static function configDir(): string
    {
        return self::$env['config_dir'];
    }

    /**
     * 获取运行时文件夹名称
     * @return string
     */
    public static function runtimeDir(): string
    {
        return self::$env['runtime_dir'];
    }

    /**
     * 获取应用控制器文件夹名称
     * @return string
     */
    public static function appControllerDir(): string
    {
        return self::$env['app_controller_dir'];
    }

    /**
     * 获取应用视图文件夹名称
     * @return string
     */
    public static function appViewDir(): string
    {
        return self::$env['app_view_dir'];
    }

    /**
     * 获取应用目录路径
     * @return string
     */
    public static function appPath(): string
    {
        return self::$env['root_path'] . '/' . self::$env['app_dir'];
    }

    /**
     * 获取配置目录路径
     * @return string
     */
    public static function configPath(): string
    {
        return self::$env['root_path'] . '/' . self::$env['config_dir'];
    }

    /**
     * 获取运行目录路径
     * @return string
     */
    public static function runtimePath(): string
    {
        return self::$env['root_path'] . '/' . self::$env['runtime_dir'];
    }
}