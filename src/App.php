<?php

namespace Fize\Framework;

use Closure;
use Fize\Cache\Cache;
use Fize\Database\Db;
use Fize\Exception\NotSetException\ParameterNotSetException;
use Fize\Framework\Exception\ActionNotFoundException;
use Fize\Framework\Exception\ControllerNotFoundException;
use Fize\Framework\Exception\ModuleNotFoundException;
use Fize\Framework\Handler\ErrorHandlerInterface;
use Fize\Framework\Handler\ExceptionHandlerInterface;
use Fize\Framework\Handler\ShutdownHandlerInterface;
use Fize\Framework\Middleware\MiddlewareInterface;
use Fize\Framework\Middleware\MiddlewarePipeline;
use Fize\IO\Directory;
use Fize\IO\OB;
use Fize\Log\Log;
use Fize\View\View;
use Fize\Web\Cookie;
use Fize\Web\Request;
use Fize\Web\Response;
use Fize\Web\Session;
use ReflectionClass;
use ReflectionException;
use Throwable;

/**
 * 应用
 */
class App
{

    /**
     * @var App 当前应用实例（单例兼容层）
     */
    protected static $instance;

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
     * @var Db|null 延迟加载的数据库实例
     */
    protected static $lazyDb;

    /**
     * 获取当前应用实例
     * @return App|null
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     * 构造。
     *
     * 在此执行所有准备流程。
     * @param array $env 环境配置
     */
    public function __construct(array $env = [])
    {
        self::$instance = $this;
        self::$microtimeStart = microtime(true);
        OB::start();
        $this->init($env);
        $this->setHandler();
        $this->registerComponent();
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
            if (isset($_GET[$route_key])) {
                $route = Request::get($route_key);
            } else {
                $route = Request::server('PATH_INFO') ?? '';
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

        // 由于需要读取分组参数所以 module 必须先确定
        $this->checkModule();

        new Config(Env::configPath(), self::$module);
    }

    /**
     * 加载中间件管道
     * @return MiddlewarePipeline
     */
    protected function loadMiddleware(): MiddlewarePipeline
    {
        $pipeline = new MiddlewarePipeline();
        $config = Config::get('middleware');
        if ($config && !empty($config['global'])) {
            foreach ($config['global'] as $middleware_class) {
                $middleware = new $middleware_class();
                if ($middleware instanceof MiddlewareInterface) {
                    $pipeline->pipe($middleware);
                }
            }
        }
        return $pipeline;
    }

    /**
     * 注册组件
     *
     * 轻量组件（Cookie、Request）在构造时初始化，
     * 重量组件（Database、Cache、Log、Session、View）建议通过延迟访问器按需加载。
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
        if ($cache_config['handler'] == 'DataBase') {  // Cache 使用 Db 处理器时的默认配置
            if (empty($cache_config['config']['database'])) {
                $cache_config['config']['database'] = $db_config;
            }
        }
        new Cache($cache_config['handler'], $cache_config['config']);

        $log_config = Config::get('log');  // Log 使用 Db 处理器时的默认配置
        if ($log_config['handler'] == 'DataBase') {  // Log 使用 Db 处理器时的默认配置
            if (empty($log_config['config']['database'])) {
                $log_config['config']['database'] = $db_config;
            }
        }
        new Log($log_config['handler'], $log_config['config']);

        $session_config = Config::get('session');
        if ($session_config['save_handler']['type'] == 'DataBase') {  // Session 使用 Db 处理器时的默认配置
            if (empty($session_config['save_handler']['config']['database'])) {
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
     * 获取数据库实例（延迟加载）
     *
     * 如已在 registerComponent() 中初始化则直接返回，否则按需创建。
     * 适用于 Swoole 等长驻进程中跳过不需要的组件初始化。
     * @return Db
     */
    public static function db(): Db
    {
        if (self::$lazyDb !== null) {
            return self::$lazyDb;
        }
        $config = Config::get('database');
        self::$lazyDb = new Db($config['type'], $config['config'], $config['mode'] ?? null);
        return self::$lazyDb;
    }

    /**
     * 接管异常处理
     */
    protected function setHandler()
    {
        // 系统错误处理
        set_error_handler(function ($errno, $errstr, $errfile = null, $errline = 0) {
            OB::clean();
            $class = Config::get('handler.error');
            /**
             * @var ErrorHandlerInterface $handler
             */
            $handler = new $class();
            return $handler->run($errno, $errstr, $errfile, $errline);
        });

        // 系统异常处理
        set_exception_handler(function (Throwable $exception) {
            $class = Config::get('handler.exception');
            /**
             * @var ExceptionHandlerInterface $handler
             */
            $handler = new $class();
            $handler->run($exception);
        });

        // 接管结束任务
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
        if (Env::get('module') === false) {  // 不使用分组
            self::$module = null;
        } elseif (Env::get('module') === true) {  // 自动判断分组
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
        $path = Env::appPath() . '/' . self::$module;
        if (self::$module && !Directory::exists($path)) {
            throw new ModuleNotFoundException(self::$module, $path);
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
        $class_path = '\\' . ucfirst(Env::appDir());
        if (self::$module) {
            $class_path .= '\\' . self::$module;
        }
        $class_path .= '\\' . Env::appControllerDir() . '\\' . $controller;
        $class = $class_path . $config_controller['controller_postfix'];
        if (!class_exists($class)) {
            $class = $class_path;
        }
        if (!class_exists($class)) {
            if ($throw) {
                throw new ControllerNotFoundException(self::$module, $controller, $class, "{$class} not found");
            }
            return false;
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
            if (Env::get('module') === true) {  // 自动判断
                array_shift($routes);  // 第一个即为模块名
            }

            if (count($routes) == 0) {  // 默认
                self::$controller = $config_controller['default_controller'];
                self::$action = $config_controller['default_action'];
            } elseif (count($routes) == 1) {  // 单个即为控制器
                self::$controller = ucfirst($routes[0]);
                self::$action = $config_controller['default_action'];
            } else {
                // 最后一个是操作
                $t_routes = $routes;
                self::$action = array_pop($t_routes);
                $t_routes[count($t_routes) - 1] = ucfirst($t_routes[count($t_routes) - 1]);
                self::$controller = implode('\\', $t_routes);
                if (!$this->checkController(self::$controller)) {  // 整个URL都是控制器
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
     *
     * 通过中间件管道包装控制器执行，支持全局中间件拦截。
     * 参数注入通过反射自动识别类型：对象类型从容器注入，标量类型从请求获取。
     * @throws ReflectionException
     */
    public function run()
    {
        View::path(self::$controller . "/" . self::$action);

        $pipeline = $this->loadMiddleware();

        $core = Closure::bind(function ($request) {
            $class = self::$class;
            $action = self::$action;
            $controller = new $class();

            $ref_class = new ReflectionClass($class);
            $ref_method = $ref_class->getMethod($action);
            $parameters = $this->resolveParameters($ref_method, $class, $action);

            $response = call_user_func_array([$controller, $action], $parameters);
            if ($response) {
                if ($response instanceof Response) {
                    return $response;
                } elseif (is_string($response)) {
                    return Response::html($response);
                } elseif (is_array($response)) {
                    return Response::json($response);
                }
            }
            return null;
        }, $this);

        $response = $pipeline->process(new Request(), $core);
        if ($response instanceof Response) {
            $response->send();
        }
        OB::endFlush();
    }

    /**
     * 解析控制器方法参数
     *
     * 通过反射识别参数类型声明：
     * - 对象类型参数：从容器/服务注入（当前为框架内置类型自动创建）
     * - 标量类型参数：从 Request::get() 获取
     * @param \ReflectionMethod $ref_method 方法反射
     * @param string            $class      控制器类名
     * @param string            $action     操作名
     * @return array 解析后的参数列表
     * @throws ParameterNotSetException 必需参数未提供时抛出
     */
    protected function resolveParameters(\ReflectionMethod $ref_method, string $class, string $action): array
    {
        $parameters = [];
        foreach ($ref_method->getParameters() as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();

            if ($type && !$type->isBuiltin()) {
                // 对象类型 → 尝试容器注入
                $type_name = $type->getName();
                $value = $this->resolveFromContainer($type_name);
                if ($value === null) {
                    if ($parameter->isOptional()) {
                        $value = $parameter->getDefaultValue();
                    } else {
                        throw new ParameterNotSetException($class, $action, $name);
                    }
                }
                $parameters[] = $value;
            } else {
                // 标量类型 → 从请求获取
                $value = Request::get($name);
                if ($parameter->isOptional()) {
                    $value = is_null($value) ? $parameter->getDefaultValue() : $value;
                } elseif (is_null($value)) {
                    throw new ParameterNotSetException($class, $action, $name);
                }
                $parameters[] = $value;
            }
        }
        return $parameters;
    }

    /**
     * 从容器解析依赖
     *
     * 当前支持框架内置类型的自动注入，后续可扩展为完整的服务容器。
     * @param string $type_name 类全限定名
     * @return object|null
     */
    protected function resolveFromContainer(string $type_name)
    {
        // 框架内置类型自动注入
        $builtins = [
            Request::class  => function () { return new Request(); },
        ];
        if (isset($builtins[$type_name])) {
            return $builtins[$type_name]();
        }
        // 通用类尝试无参构造
        if (class_exists($type_name)) {
            $ref = new ReflectionClass($type_name);
            if ($ref->isInstantiable() && $ref->getConstructor() === null) {
                return new $type_name();
            }
        }
        return null;
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
