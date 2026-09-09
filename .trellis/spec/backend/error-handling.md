# Error Handling

> 框架用 PHP 原生 handler + 可替换的三类处理器。控制器里的「成功/失败/跳转」也是异常，但是正常响应，不是故障。

## 处理器挂载

`App::setHandler()` 用闭包 `use ($app)`，从 `$app->config` 读类名，再用 `$app->container()->make($class)` 实例化：

| 配置键 | PHP 钩子 | 默认类 |
|--------|----------|--------|
| `handler.error` | `set_error_handler` | `Fize\Framework\Handler\ErrorHandler` |
| `handler.exception` | `set_exception_handler` | `Fize\Framework\Handler\ExceptionHandler` |
| `handler.shutdown` | `register_shutdown_function` | `Fize\Framework\Handler\ShutdownHandler` |

配置：`app/config/handler.php`。替换时实现对应 `*HandlerInterface`，再在应用 `config/handler.php` 改类名。

## 框架异常

命名空间 `Fize\Framework\Exception`，在路由检查阶段抛出：

| 类 | 何时 |
|----|------|
| `ModuleNotFoundException` | 模块目录不存在（`App::checkModule()`） |
| `ControllerNotFoundException` | 控制器类不存在（`checkController(..., true)`） |
| `ActionNotFoundException` | 方法不存在（`App::check()`） |

它们继承 `fize/exception` 的 `NotFoundException` 等类型。构造函数带模块/控制器/路径，供页面或日志使用（见 `ModuleNotFoundException::module()`）。

容器：`ContainerNotFoundException`（PSR-11 未找到）、`ContainerException`（装配失败）。在 `src/Exception/`。

Env：缺少 `root_path` 抛 PHP 自带 `InvalidArgumentException`。

## ExceptionHandler 分支

`src/Handler/ExceptionHandler.php` 按类型分流，**不要把 HttpResponseException 当错误记日志**：

1. `HttpResponseException`：取出 Response 并 `send()`（`Controller::result/success/error/redirect` 的出路）
2. `PageNotFoundException`：`Log::notice`，渲染 `app/view/404.php`，HTTP 404
3. 其它：`Log::error`（含 code、message、file、line），渲染 `app/view/exception_handler.php`，HTTP 500

`ErrorHandler` 记 `Log::error` 后渲染 `error_handler` 模板，HTTP 500。

内置页目前**不按 `debug` 隐藏堆栈**（`docs/todo.md` 4.4 仍待做）。新增生产脱敏时走该条目，不要只改一处 handler 而漏掉视图。

## ShutdownHandler

`src/Handler/ShutdownHandler.php`：

```php
$app = App::getInstance();
$debug = $app ? $app->env->get('debug') : false;
if ($debug) {
    Log::info('耗时：' . App::timeTaken());
}
```

Handler 可能在 App 未就绪时运行，必须空安全。

## 控制器响应

需要输出响应就抛 `Fize\Exception\HttpException\HttpResponseException`，不要 `echo` 后 `return` 混用（demo 里仍有 `echo` 的示例页，那是演示不是基类约定）。

JSON 约定（`Controller::result`）：`{code, message, data}`，HTTP 仍由 `Response::json` 决定（当前为 200）。改 API 形态见 `docs/todo.md` 6.3，未立项不要改签名。

## 反模式

- 在 Handler 里再 `new Env` / 静态读配置
- 把 `HttpResponseException` 记成 `Log::error`
- `make($handlerClass)` 改回 `new $class()` 且假定无依赖
- 测试里构造 `App` 后不 `restore_error_handler()` / `restore_exception_handler()`（PHPUnit 会标 risky，见 `tests/TestApp.php`）
