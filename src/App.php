<?php

namespace fize\framework;

use ReflectionClass;
use Throwable;
use fize\cache\Cache;
use fize\db\Db;
use fize\io\Directory;
use fize\io\Ob;
use fize\log\Log;
use fize\view\View;
use fize\web\Request;
use fize\web\Cookie;
use fize\web\Session;
use fize\web\Response;
use fize\framework\exception\ResponseException;
use fize\framework\exception\NotFoundException;
use fize\framework\exception\ModuleNotFoundException;
use fize\framework\exception\ControllerNotFoundException;
use fize\framework\exception\ActionNotFoundException;
use fize\framework\exception\ParameterNotSetException;

/**
 * 应用入口
 */
class App
{

    /**
     * @var array 环境配置
     */
    protected static $env;

    /**
     * @var string 当前分组
     */
    protected static $module;

    /**
     * @var string 当前控制器
     */
    protected static $controller;

    /**
     * @var string 当前操作
     */
    protected static $action;

    /**
     * @var string 当前控制器类全限定名
     */
    protected static $class;

    /**
     * 在此执行所有准备流程
     * @param array $env 环境配置
     */
    public function __construct(array $env = [])
    {
        $this->init($env);
        $this->config();
        $this->handler();
        $this->check();
    }

    /**
     * 获取实际路由地址
     * @return string
     */
    protected static function getRoute()
    {
        static $route = null;
        if (is_null($route)) {
            if (isset($_GET[self::$env['route_key']]) && !is_null($_GET[self::$env['route_key']])) {
                $route = Request::get(self::$env['route_key']);
            } else {
                $route = Request::server('PATH_INFO');
                if ($route) {
                    //删除第一个字符'/'
                    $route = substr($route, 1);
                    //删除最后一个字符'/'
                    if (substr($route, -1) == '/') {
                        $route = substr($route, 0, -1);
                    }
                } else {
                    $route = '';
                }
            }
        }
        return $route;
    }

    /**
     * 初始化
     * @param array $env 参数
     */
    protected function init(array $env)
    {
        Ob::start();
        $default_env = [
            'root_path'          => null,  //根目录
            'app_dir'            => 'app',  //应用文件夹
            'config_dir'         => 'config',  //配置文件夹
            'runtime_dir'        => 'runtime',  //运行时文件夹
            'app_controller_dir' => 'controller',  //控制器文件夹
            'app_view_dir'       => 'view',  //视图文件夹
            'module'             => true,  //true表示开启分组并自动判断，false表示关闭分组，字符串表示指定分组
            'default_module'     => 'index',  //开启分组时的默认分组
            'route_key'          => '_r',  //兼容模式路由GET参数名
        ];
        $env = array_merge($default_env, $env);

        if (is_null($env['root_path'])) {
            $root_path = dirname(dirname(dirname(dirname(dirname(__FILE__)))));  //使用composer放置在vendor文件夹中的相对位置
            $env['root_path'] = $root_path;
        }

        self::$env = $env;

        //由于需要读取分组参数所以 module 必须先确认
        $this->checkModule();
    }

    /**
     * 载入配置
     */
    protected function config()
    {
        new Config(self::configPath(), self::$module);

        $url_config = Config::get('url');
        new Url($url_config);

        $cookie_config = Config::get('cookie');
        new Cookie($cookie_config);

        $request_config = Config::get('request');
        new Request($request_config);

        $db_config = Config::get('db');
        if ($db_config) {
            $db_mode = isset($db_config['mode']) ? $db_config['mode'] : null;
            new Db($db_config['type'], $db_config['config'], $db_mode);
        }

        $cache_config = Config::get('cache');
        if ($cache_config['handler'] == 'DataBase') {  // Cahce 使用 Db 处理器时的默认配置
            if (!isset($cache_config['config']['db']) && empty($cache_config['config']['db'])) {
                $cache_config['config']['db'] = $db_config;
            }
        }
        new Cache($cache_config['handler'], $cache_config['config']);

        $log_config = Config::get('log');  // Log 使用 Db 处理器时的默认配置
        if ($log_config['handler'] == 'DataBase') {  // Cahce 使用 Db 处理器时的默认配置
            if (!isset($log_config['config']['db']) && empty($log_config['config']['db'])) {
                $log_config['config']['db'] = $db_config;
            }
        }
        new Log($log_config['handler'], $log_config['config']);

        $session_config = Config::get('session');
        if ($session_config['save_handler']['type'] == 'DataBase') {  // Session 使用 Db 处理器时的默认配置
            if (!isset($session_config['save_handler']['config']['db']) && empty($session_config['save_handler']['config']['db'])) {
                $session_config['save_handler']['config']['db'] = $db_config;
            }
        }
        new Session($session_config);

        $path_dir = self::$module ? self::appPath() . '/' . self::$module . '/' . self::$env['app_view_dir'] : App::appPath() . '/' . self::$env['app_view_dir'];
        if (Directory::isDir($path_dir)) {
            $config_view = Config::get('view');
            new View($config_view['handler'], $config_view['config']);
        }
    }

    /**
     * 接管异常处理
     */
    protected function handler()
    {
        //系统错误处理
        set_error_handler(function ($errno, $errstr, $errfile = null, $errline = 0, array $errcontext = []) {
            Ob::clean();
            Log::error("[{$errno}]$errstr : {$errfile} Line: {$errline}");
            $view = View::getInstance('Php', ['view' => __DIR__ . '/view']);
            $view->assign('errno', $errno);
            $view->assign('errstr', $errstr);
            $view->assign('errfile', $errfile);
            $view->assign('errline', $errline);
            $view->assign('errcontext', $errcontext);
            $response = Response::html($view->render('error_handler'));
            $response->withStatus(500)->send();
            die();
        }, E_ALL);

        //系统异常处理
        set_exception_handler(function (Throwable $exception) {
            if ($exception instanceof ResponseException) {
                $response = $exception->getResponse();
                $response->send();
            } elseif ($exception instanceof NotFoundException) {
                Log::notice("[404]Not Found[{$exception->getMessage()}] : {$exception->url()}");
                $view = View::getInstance('Php', ['view' => __DIR__ . '/view']);
                $view->assign('exception', $exception);
                $response = Response::html($view->render('404'));
                $response->withStatus(404)->send();
            } else {
                Log::error("[{$exception->getCode()}]{$exception->getMessage()} : {$exception->getFile()} Line: {$exception->getLine()}");
                $view = View::getInstance('Php', ['view' => __DIR__ . '/view']);
                $view->assign('exception', $exception);
                $response = Response::html($view->render('exception_handler'));
                $response->withStatus(500)->send();
            }
        });

        //接管结束任务
        register_shutdown_function(function () {
            // @todo 收尾工作
        });
    }

    /**
     * 检测并确定模块
     */
    protected function checkModule()
    {
        if (self::$env['module'] === false) {  //不使用分组
            self::$module = null;
        } elseif (self::$env['module'] === true) {  //自动判断分组
            $route = self::getRoute();
            if ($route) {
                $routes = explode('/', $route);
                self::$module = $routes[0];
            } else {
                self::$module = self::$env['default_module'];
            }
        } else {
            self::$module = self::$env['module'];
        }
        if (self::$module && !Directory::isDir(self::appPath() . '/' . self::$module)) {
            throw new ModuleNotFoundException(self::$module);
        }
    }

    /**
     * 检测控制器是否可用
     * @param string $controller 控制器名
     * @param bool   $throw      如果控制器不存在是否抛出错误
     * @return bool
     */
    protected function checkController($controller, $throw = false)
    {
        $config_controller = Config::get('controller');
        $class_path = '\\' . self::$env['app_dir'];
        if (self::$module) {
            $class_path .= '\\' . self::$module;
        }
        $class_path .= '\\' . self::$env['app_controller_dir'] . '\\' . $controller;
        $class = str_replace('\\', DIRECTORY_SEPARATOR, $class_path . $config_controller['controller_postfix']);
        if (!class_exists($class)) {
            $class = str_replace('\\', DIRECTORY_SEPARATOR, $class_path);
            if (!class_exists($class)) {
                if ($throw) {
                    throw new ControllerNotFoundException(self::$module, $controller);
                }
                return false;
            }
        }
        self::$class = $class;
        return true;
    }

    /**
     * 进行检测并确定各参数值
     */
    protected function check()
    {
        $config_controller = Config::get('controller');
        $route = self::getRoute();

        if ($route) {
            $routes = explode('/', $route);
            if (self::$env['module'] === true) {  //自动判断
                array_shift($routes);  //第一个即为模块名
            }

            if (count($routes) == 0) {  //默认
                self::$controller = $config_controller['default_controller'];
                self::$action = $config_controller['default_action'];
            } elseif (count($routes) == 1) {  //单个即为控制器
                self::$controller = ucfirst($routes[0]);
                self::$action = $config_controller['default_action'];
            } else {
                //最后一个是操作
                $t_routes = $routes;
                self::$action = array_pop($t_routes);
                $t_routes[count($t_routes) - 1] = ucfirst($t_routes[count($t_routes) - 1]);
                self::$controller = implode('\\', $t_routes);
                if (!self::checkController(self::$controller)) {  //整个URL都是控制器
                    self::$controller = implode('\\', $routes);
                    self::$action = $config_controller['default_action'];
                }
            }
        } else {
            self::$controller = $config_controller['default_controller'];
            self::$action = $config_controller['default_action'];
        }

        self::checkController(self::$controller, true);
        if (!method_exists(self::$class, self::$action)) {
            throw new ActionNotFoundException(self::$module, self::$controller, self::$action);
        }
    }

    /**
     * 执行逻辑
     */
    public function run()
    {
        View::path(self::$controller . "/" . self::$action);

        $class = self::$class;
        $action = self::$action;
        $controller = new $class();

        $ref_class = new ReflectionClass($class);
        $ref_method = $ref_class->getMethod($action);
        $parameters = [];
        foreach ($ref_method->getParameters() as $parameter) {
            $name = $parameter->getName();
            $value = Request::get($name);
            if ($parameter->isOptional()) {
                $value = is_null($value) ? $parameter->getDefaultValue() : $value;
            } elseif (is_null($value)) {
                throw new ParameterNotSetException($class, $action, $name);
            }
            $parameters[] = $value;
        }

        $response = call_user_func_array([$controller, $action], $parameters);
        if ($response) {
            if ($response instanceof Response) {
                $response->send();
            } elseif (is_string($response)) {
                $response = Response::html($response);
                $response->send();
            } elseif (is_array($response)) {
                $response = Response::json($response);
                $response->send();
            }
        }
        Ob::endFlush();
    }

    /**
     * 获取底层框架配置
     * @param string $key 如果指定该值，则返回该值指定的配置
     * @return mixed
     */
    public static function env($key = null)
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
    public static function rootPath()
    {
        return self::$env['root_path'];
    }

    /**
     * 获取应用目录路径
     * @return string
     */
    public static function appPath()
    {
        return self::$env['root_path'] . '/' . self::$env['app_dir'];
    }

    /**
     * 获取配置目录路径
     * @return string
     */
    public static function configPath()
    {
        return self::$env['root_path'] . '/' . self::$env['config_dir'];
    }

    /**
     * 获取运行目录路径
     * @return string
     */
    public static function runtimePath()
    {
        return self::$env['root_path'] . '/' . self::$env['runtime_dir'];
    }

    /**
     * 获取当前模块名
     *
     * 未启用模块时返回 null
     * @return string
     */
    public static function module()
    {
        return self::$module;
    }

    /**
     * 获取当前控制器
     * @return string
     */
    public static function controller()
    {
        return self::$controller;
    }

    /**
     * 获取当前操作
     * @return string
     */
    public static function action()
    {
        return self::$action;
    }
}
