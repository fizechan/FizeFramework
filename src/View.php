<?php


namespace fize\framework;

use fize\view\View as FizeView;
use fize\view\ViewHandler;

/**
 * 视图类
 * @package fize\framework
 */
class View
{

    /**
     * @var ViewHandler
     */
    protected static $view;

    /**
     * @var string 模板路径
     */
    protected static $path;

    /**
     * 在构造方法中设置静态属性
     * @param array $config 配置项
     */
    public function __construct(array $config = [])
    {
        self::$view = FizeView::getInstance($config['handler'], $config['config']);
    }

    /**
     * 获取底部引擎对象
     * @return mixed
     */
    public static function engine()
    {
        return self::$view->engine();
    }

    /**
     * 设置模板路径
     * @param string $path 模板路径
     */
    public static function path($path)
    {
        self::$path = $path;
    }

    /**
     * 变量赋值
     * @param string $name 变量名
     * @param mixed $value 变量
     */
    public static function assign($name, $value)
    {
        self::$view->assign($name, $value);
    }

    /**
     * 返回渲染内容
     * @param string $path 模板文件路径
     * @param array $assigns 指定变量赋值
     * @return string
     */
    public static function render($path = null, array $assigns = [])
    {
        if ($path) {
            self::path($path);
        }
        return self::$view->render(self::$path, $assigns);
    }

    /**
     * 显示渲染内容
     * @deprecated 使用render方法统一调用即可
     * @param string $path 模板文件路径
     * @param array $assigns 指定变量赋值
     */
    public static function display($path = null, array $assigns = [])
    {
        if ($path) {
            self::path($path);
        }
        self::$view->display(self::$path, $assigns);
    }
}