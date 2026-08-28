<?php

namespace Fize\Framework;

use InvalidArgumentException;

/**
 * 环境
 */
class Env
{

    /**
     * @var array 环境配置
     */
    protected $env;

    /**
     * 构造
     * @param array $env 环境配置，必须包含 root_path
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
            'app_controller_dir' => 'Controller',  // 控制器文件夹
            'app_view_dir'       => 'View',  // 视图文件夹
            'module'             => true,  // true表示开启分组并自动判断，false表示关闭分组，字符串表示指定分组
            'default_module'     => 'Index',  // 开启分组时的默认分组
            'route_key'          => '_r',  // 兼容模式路由GET参数名
            'debug'              => false,  // 是否调试模式
        ];
        $env = array_merge($default_env, $env);

        if ($env['root_path'] === null || $env['root_path'] === '') {
            throw new InvalidArgumentException('Env root_path is required');
        }

        $this->env = $env;
    }

    /**
     * 获取底层框架配置
     * @param string|null $key 如果指定该值，则返回该值指定的配置
     * @return mixed
     */
    public function get(string $key = null)
    {
        if ($key) {
            return $this->env[$key] ?? null;
        }
        return $this->env;
    }

    /**
     * 获取根目录路径
     * @return string
     */
    public function rootPath(): string
    {
        return $this->env['root_path'];
    }

    /**
     * 获取应用文件夹名称
     * @return string
     */
    public function appDir(): string
    {
        return $this->env['app_dir'];
    }

    /**
     * 获取配置文件夹名称
     * @return string
     */
    public function configDir(): string
    {
        return $this->env['config_dir'];
    }

    /**
     * 获取运行时文件夹名称
     * @return string
     */
    public function runtimeDir(): string
    {
        return $this->env['runtime_dir'];
    }

    /**
     * 获取应用控制器文件夹名称
     * @return string
     */
    public function appControllerDir(): string
    {
        return $this->env['app_controller_dir'];
    }

    /**
     * 获取应用视图文件夹名称
     * @return string
     */
    public function appViewDir(): string
    {
        return $this->env['app_view_dir'];
    }

    /**
     * 获取应用目录路径
     * @return string
     */
    public function appPath(): string
    {
        return $this->env['root_path'] . '/' . $this->env['app_dir'];
    }

    /**
     * 获取配置目录路径
     * @return string
     */
    public function configPath(): string
    {
        return $this->env['root_path'] . '/' . $this->env['config_dir'];
    }

    /**
     * 获取运行目录路径
     * @return string
     */
    public function runtimePath(): string
    {
        return $this->env['root_path'] . '/' . $this->env['runtime_dir'];
    }

    /**
     * 供 Config 插值的占位符映射
     * @param string|null $module 当前模块名
     * @return array
     */
    public function parameters(string $module = null): array
    {
        $module_name = $module ?: '';
        $app_path = $this->appPath();
        return [
            '%root_path%'          => $this->rootPath(),
            '%app_path%'           => $app_path,
            '%config_path%'        => $this->configPath(),
            '%runtime_path%'       => $this->runtimePath(),
            '%app_dir%'            => $this->appDir(),
            '%app_view_dir%'       => $this->appViewDir(),
            '%app_controller_dir%' => $this->appControllerDir(),
            '%module%'             => $module_name,
            '%module_path%'        => $module_name !== '' ? $app_path . '/' . $module_name : $app_path,
        ];
    }
}
