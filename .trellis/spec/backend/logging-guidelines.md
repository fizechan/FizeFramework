# Logging Guidelines

> 日志组件是 `fize/log` 的 `Fize\Log\Log`。框架在 Handler 与 Shutdown 里调用它；配置在 `config/log.php`。

## 初始化

`App::registerComponent()` 始终 `new Log($handler, $config)`。默认文件驱动（`app/config/log.php`）：

```php
return [
    'handler' => 'File',
    'config'  => [
        'path'     => '%runtime_path%/log',
        'file'     => date('Ymd') . '.log',
        'max_size' => 2 * 1024 * 1024,
    ],
];
```

`handler == 'DataBase'` 时，若未配 `config.database`，会套用 `database` 配置。

## 现有级别用法（按代码，不是理想模型）

| 调用 | 位置 | 含义 |
|------|------|------|
| `Log::error` | `ErrorHandler`、`ExceptionHandler` 非 404 分支 | 系统错误 / 未处理异常，带文件行号 |
| `Log::notice` | `ExceptionHandler` 的 404 分支 | 页面不存在 |
| `Log::info` | `ShutdownHandler` 且 `debug === true` | 请求耗时 |

新代码对齐这三档：故障 `error`，可预期的未找到 `notice`，调试信息用 `info` 并受 `env.debug` 或明确配置约束。仓库里还没有 `Log::debug` / `warning` 的框架调用，不要为了「完整级别表」凭空铺开。

## 写什么

- 异常：`[code]message : file Line: line`（已有格式，新日志不要另起一套无法检索的散文）
- Shutdown：只在 debug 记耗时，不要在生产默认路径打每个请求的 info

## 不写什么

- 密码、Cookie、Session id、数据库整段 DSN
- 把 `HttpResponseException`（业务成功/跳转）写成 error
- 在配置文件里用 `Env::runtimePath()` 拼日志目录（用 `%runtime_path%`）

## 反模式

- 控制器里直接 `echo` 调试信息充当日志（demo 里有 `var_dump`，不要复制到框架核心）
- 新建第二套 logger，绕过 `Log`
- ShutdownHandler 在 `App::getInstance()` 为 null 时解引用 `$app->env`
