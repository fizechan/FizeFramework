# 日志规范

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
| `Log::error` | `ErrorHandler`、`ExceptionHandler` 的非 404 分支 | 系统错误 / 未处理异常，包含文件和行号 |
| `Log::notice` | `ExceptionHandler` 的 404 分支 | 页面不存在 |
| `Log::info` | `ShutdownHandler` 且 `debug === true` | 请求耗时 |

新代码对齐这三档：故障 `error`，可预期的未找到 `notice`，调试信息用 `info` 并受 `env.debug` 或明确配置约束。仓库里还没有 `Log::debug` / `warning` 的框架调用，不要为了「完整级别表」凭空铺开。

## 应记录的内容

- 异常：`[code]message : file Line: line`（已有格式，新日志不要另起一套无法检索的散文）
- Shutdown：只在 debug 模式记录耗时；不要在生产环境的默认路径为每个请求记录 info

## 不应记录的内容

- 密码、Cookie、Session ID、完整数据库 DSN
- 不要把 `HttpResponseException`（业务成功 / 跳转）记为 error
- 在配置文件里用 `Env::runtimePath()` 拼日志目录（用 `%runtime_path%`）

## 反模式

- 控制器里直接 `echo` 调试信息充当日志（demo 中有 `var_dump` 示例，不要复制到框架核心）
- 新建第二套 logger，绕过 `Log` 组件
- ShutdownHandler 在 `App::getInstance()` 为 null 时解引用 `$app->env`
