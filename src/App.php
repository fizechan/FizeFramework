<?php

namespace fize\framework;

use ReflectionClass;
use Throwable;
use fize\cache\Cache;
use fize\database\Db;
use fize\io\Directory;
use fize\io\Ob;
use fize\log\Log;
use fize\view\View;
use fize\web\Cookie;
use fize\web\Request;
use fize\web\Response;
use fize\web\Session;
use fize\framework\exception\ActionNotFoundException;
use fize\framework\exception\ControllerNotFoundException;
use fize\framework\exception\ModuleNotFoundException;
use fize\framework\exception\ParameterNotSetException;
use fize\framework\handler\ErrorHandlerInterface;
use fize\framework\handler\ExceptionHandlerInterface;
use fize\framework\handler\ShutdownHandlerInterface;

/**
 * 应用
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
     * @var float 程序启动时时间戳
     */
    protected static $microtimeStart;

    /**
     * 构造。在此执行所有准备流程
     * @param array $env 环境配置
     */
    public function __construct(array $env = [])
    {
        self::$microtimeStart = microtime(true);
        Ob::start();
        $this->EnvInit($env);
        $this->registerComponent();
        $this->setHandler();
        $this->check();
    }

    /**
     * 析构
     */
    public function __destruct()
    {
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
                $route = Url::parse($route);
                if ($route) {
                    // 删除第一个字符'/'
                    $route = substr($route, 1);
                    // 删除最后一个字符'/'
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
     * 环境初始化
     * @param array $env 参数
     */
    protected function EnvInit(array $env)
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
            $root_path = dirname(dirname(dirname(dirname(dirname(__FILE__)))));  // 使用composer放置在vendor文件夹中的相对位置
            $env['root_path'] = $root_path;
        }

        self::$env = $env;

        // URL配置仅顶层有效
        $url_config = Config::get('url');
        new Url($url_config);

        // 由于需要读取分组参数所以 module 必须先确认
        $this->checkModule();

        new Config(self::configPath(), self::$module);
    }

    /**
     * 注册组件
     */
    protected function registerComponent()
    {
        $cookie_config = Config::get('cookie');
        new Cookie($cookie_config);

        $request_config = Config::get('request');
        new Request($request_config);

        $db_config = Config::get('database');
        if ($db_config) {
            $db_mode = isset($db_config['mode']) ? $db_config['mode'] : null;
            new Db($db_config['type'], $db_config['config'], $db_mode);
        }

        $cache_config = Config::get('cache');
        if ($cache_config['handler'] == 'DataBase') {  // Cahce 使用 Db 处理器时的默认配置
            if (!isset($cache_config['config']['database']) && empty($cache_config['config']['database'])) {
                $cache_config['config']['database'] = $db_config;
            }
        }
        new Cache($cache_config['handler'], $cache_config['config']);

        $log_config = Config::get('log');  // Log 使用 Db 处理器时的默认配置
        if ($log_config['handler'] == 'DataBase') {  // Cahce 使用 Db 处理器时的默认配置
            if (!isset($log_config['config']['database']) && empty($log_config['config']['database'])) {
                $log_config['config']['database'] = $db_config;
            }
        }
        new Log($log_config['handler'], $log_config['config']);

        $session_config = Config::get('session');
        if ($session_config['save_handler']['type'] == 'DataBase') {  // Session 使用 Db 处理器时的默认配置
            if (!isset($session_config['save_handler']['config']['database']) && empty($session_config['save_handler']['config']['database'])) {
                $session_config['save_handler']['config']['database'] = $db_config;
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
    protected function setHandler()
    {
        //系统错误处理
        set_error_handler(function ($errno, $errstr, $errfile = null, $errline = 0) {
            Ob::clean();
            $class = Config::get('handler.error');
            /**
             * @var ErrorHandlerInterface $handler
             */
            $handler = new $class();
            return $handler->run($errno, $errstr, $errfile, $errline);
        }, E_ALL);

        //系统异常处理
        set_exception_handler(function (Throwable $exception) {
            $class = Config::get('handler.exception');
            /**
             * @var ExceptionHandlerInterface $handler
             */
            $handler = new $class();
            $handler->run($exception);
        });

        //接管结束任务
        register_shutdown_function(function () {
            $class = Config::get('handler.shutdown');
            /**
             * @var ShutdownHandlerInterface $handler
             */
            $handler = new $class();
            $handler->run();
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
                if (!$this->checkController(self::$controller)) {  //整个URL都是控制器
                    self::$controller = implode('\\', $routes);
                    self::$action = $config_controller['default_action'];
                }
            }
        } else {
            self::$controller = $config_controller['default_controller'];
            self::$action = $config_controller['default_action'];
        }

        $this->checkController(self::$controller, true);
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
     * @return string|null 未启用模块时返回 null
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

    /**
     * 获取当前程序执行所用时间
     * @return float
     */
    public static function timeTaken()
    {
        $microtimeNow = microtime(true);
        return $microtimeNow - self::$microtimeStart;
    }
}
