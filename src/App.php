<?php

namespace Fize\Framework;

use Fize\Cache\Cache;
use Fize\Database\Db;
use Fize\Framework\Exception\ActionNotFoundException;
use Fize\Framework\Exception\ControllerNotFoundException;
use Fize\Framework\Exception\ModuleNotFoundException;
use Fize\Framework\Exception\ParameterNotSetException;
use Fize\Framework\HandlerInterface\ErrorHandlerInterface;
use Fize\Framework\HandlerInterface\ExceptionHandlerInterface;
use Fize\Framework\HandlerInterface\ShutdownHandlerInterface;
use Fize\IO\Directory;
use Fize\IO\OB;
use Fize\Log\Log;
use Fize\View\View;
use Fize\Web\Cookie;
use Fize\Web\Request;
use Fize\Web\Response;
use Fize\Web\Session;
use ReflectionClass;
use Throwable;

/**
 * 应用
 */
class App
{

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
     * 构造。
     *
     * 在此执行所有准备流程。
     * @param array $env 环境配置
     */
    public function __construct(array $env = [])
    {
        self::$microtimeStart = microtime(true);
        OB::start();
        $this->init($env);
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
            $route_key = Env::get('route_key');
            if (isset($_GET[$route_key]) && !is_null($_GET[$route_key])) {
                $route = Request::get($route_key);
            } else {
                $route = Request::server('PATH_INFO');
            }
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
        return $route;
    }

    /**
     * 初始化
     * @param array $env 参数
     */
    protected function init(array $env)
    {
        new Env($env);

        // URL配置仅顶层有效
        new Config(Env::configPath());
        $url_config = Config::get('url');
        new Url($url_config);

        // 由于需要读取分组参数所以 module 必须先确认
        $this->checkModule();

        new Config(Env::configPath(), self::$module);
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
            $db_mode = $db_config['mode'] ?? null;
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

        $path_dir = self::$module ? Env::appPath() . '/' . self::$module . '/' . Env::appViewDir() : Env::appPath() . '/' . Env::appViewDir();
        if (Directory::exists($path_dir)) {
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
            OB::clean();
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
        if (Env::get('module') === false) {  //不使用分组
            self::$module = null;
        } elseif (Env::get('module') === true) {  //自动判断分组
            $route = self::getRoute();
            if ($route) {
                $routes = explode('/', $route);
                self::$module = $routes[0];
            } else {
                self::$module = Env::get('default_module');
            }
        } else {
            self::$module = Env::get('module');
        }
        if (self::$module && !Directory::exists(Env::appPath() . '/' . self::$module)) {
            throw new ModuleNotFoundException(self::$module);
        }
    }

    /**
     * 检测控制器是否可用
     * @param string $controller 控制器名
     * @param bool   $throw      如果控制器不存在是否抛出错误
     * @return bool
     */
    protected function checkController(string $controller, bool $throw = false): bool
    {
        $config_controller = Config::get('controller');
        $class_path = '\\' . Env::appDir();
        if (self::$module) {
            $class_path .= '\\' . self::$module;
        }
        $class_path .= '\\' . Env::appControllerDir() . '\\' . $controller;
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
            if (Env::get('module') === true) {  //自动判断
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
        View::path(self::$controller . '/' . self::$action);

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
        OB::endFlush();
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
    public static function controller(): string
    {
        return self::$controller;
    }

    /**
     * 获取当前操作
     * @return string
     */
    public static function action(): string
    {
        return self::$action;
    }

    /**
     * 获取当前程序执行所用时间
     * @return float
     */
    public static function timeTaken(): float
    {
        $microtime_now = microtime(true);
        return $microtime_now - self::$microtimeStart;
    }
}
