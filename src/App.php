<?php


namespace fize\framework;

use Throwable;
use fize\framework\exception\ResponseException;
use fize\framework\exception\NotFoundException;
use fize\framework\exception\ModuleNotFoundException;
use fize\framework\exception\ControllerNotFoundException;
use fize\framework\exception\ActionNotFoundException;
use fize\io\Directory;
use fize\io\Ob;
use fize\cache\Cache;
use fize\web\Request;
use fize\web\Cookie;
use fize\web\Session;
use fize\web\Response;
use fize\db\Db;
use fize\log\Log;
use fize\view\View;


/**
 * 应用入口
 */
class App
{

    /**
     * @var array 环境配置
     */
    protected static $config;

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
     * @param array $config 环境配置
     */
    public function __construct(array $config = [])
    {
        Ob::start();
        $this->init($config);
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
        if (isset($_GET[self::$config['route_key']]) && !is_null($_GET[self::$config['route_key']])) {
            $route = Request::get(self::$config['route_key']);
        } else {
            $route = Request::server('PATH_INFO');
            if ($route) {
                $route = substr($route, 1);  //删除第一个字符'/'
            } else {
                $route = '';
            }
        }
        return $route;
    }

    /**
     * 初始化
     * @param array $config 参数
     */
    protected function init(array $config)
    {
        $default_config = [
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
        $config = array_merge($default_config, $config);

        if (is_null($config['root_path'])) {
            $root_path = dirname(dirname(dirname(dirname(dirname(__FILE__)))));  //使用composer放置在vendor文件夹中的相对位置
            $config['root_path'] = $root_path;
        }

        self::$config = $config;

        if ($config['module'] === false) {  //不使用分组
            self::$module = null;
        } elseif ($config['module'] === true) {  //自动判断分组
            $route = self::getRoute();
            if ($route) {
                $routes = explode('/', $route);
                self::$module = $routes[0];
            } else {
                self::$module = $config['default_module'];
            }
        } else {
            self::$module = $config['module'];
        }
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

        $path_dir = self::$module ? self::appPath() . '/' . self::$module . '/' . self::$config['app_view_dir'] : App::appPath() . '/' . self::$config['app_view_dir'];
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
            $response->code(500);
            $response->send();
            die();
        }, E_ALL);

        //系统异常处理
        set_exception_handler(function (Throwable $exception) {
            if ($exception instanceof ResponseException) {
                $response = $exception->getResponse();
                $response->send();
            } elseif ($exception instanceof NotFoundException) {
                Log::notice("[404]Not Found : {$exception->url()}");
                $view = View::getInstance('Php', ['view' => __DIR__ . '/view']);
                $view->assign('exception', $exception);
                $response = Response::html($view->render('404'));
                $response->code(404);
                $response->send();
            } else {
                Log::error("[{$exception->getCode()}]{$exception->getMessage()} : {$exception->getFile()} Line: {$exception->getLine()}");
                $view = View::getInstance('Php', ['view' => __DIR__ . '/view']);
                $view->assign('exception', $exception);
                $response = Response::html($view->render('exception_handler'));
                $response->code(500);
                $response->send();
            }
        });

        //接管结束任务
        register_shutdown_function(function () {
            // @todo 收尾工作
            Log::info('结束');
        });
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
            if (self::$config['module'] === true) {  //自动判断
                array_shift($routes);  //第一个即为模块名
            }
            if (count($routes) == 0) {
                self::$controller = $config_controller['default_controller'];
                self::$action = $config_controller['default_action'];
            } elseif (count($routes) == 1) {
                self::$controller = ucfirst($routes[0]);
                self::$action = $config_controller['default_action'];
            } else {
                self::$action = array_pop($routes);
                $routes[count($routes) - 1] = ucfirst($routes[count($routes) - 1]);
                self::$controller = implode('/', $routes);
            }
        } else {
            self::$controller = $config_controller['default_controller'];
            self::$action = $config_controller['default_action'];
        }
        $class_path = '\\' . self::$config['app_dir'];
        if (self::$module) {
            if (!Directory::isDir(self::appPath() . '/' . self::$module)) {
                throw new ModuleNotFoundException(self::$module);
            }
            $class_path .= '\\' . self::$module;
        }
        $class_path .= '\\' . self::$config['app_controller_dir'] . '\\' . self::$controller;
        $class = str_replace('\\', DIRECTORY_SEPARATOR, $class_path . $config_controller['controller_postfix']);
        if (!class_exists($class)) {
            $class = str_replace('\\', DIRECTORY_SEPARATOR, $class_path);
            if (!class_exists($class)) {
                throw new ControllerNotFoundException(self::$module, self::$controller);
            }
        }
        if (!method_exists($class, self::$action)) {
            throw new ActionNotFoundException(self::$module, self::$controller, self::$action);
        }
        self::$class = $class;
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
        $response = $controller->$action();

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
            return self::$config[$key];
        }
        return self::$config;
    }

    /**
     * 获取根目录路径
     * @return string
     */
    public static function rootPath()
    {
        return self::$config['root_path'];
    }

    /**
     * 获取应用目录路径
     * @return string
     */
    public static function appPath()
    {
        return self::$config['root_path'] . '/' . self::$config['app_dir'];
    }

    /**
     * 获取配置目录路径
     * @return string
     */
    public static function configPath()
    {
        return self::$config['root_path'] . '/' . self::$config['config_dir'];
    }

    /**
     * 获取运行目录路径
     * @return string
     */
    public static function runtimePath()
    {
        return self::$config['root_path'] . '/' . self::$config['runtime_dir'];
    }

    /**
     * 获取当前模块名
     *
     * 未启用模块时返回null
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