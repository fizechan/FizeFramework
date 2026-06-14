# FizeFramework 功能与优化建议

## 一、Bug 与代码缺陷

### 1.1 `Url::convertQuery()` 越界访问（严重）

**文件**: `src/Url.php` 第30-38行

当 query 参数缺少 `=` 号时（如 `key1=val1&key2`），`explode('=', $param)` 仅返回一个元素，`$item[1]` 将触发 `Undefined offset` 警告。

```php
// 当前写法
$item = explode('=', $param);
$params[$item[0]] = $item[1];  // 可能越界

// 建议修复
$item = explode('=', $param, 2);
$params[$item[0]] = $item[1] ?? '';
```

### 1.2 `Url::parse()` 接收 null 参数（严重）

**文件**: `src/App.php` 第92行 → `src/Url.php` 第46行

`Request::server('PATH_INFO')` 可能返回 `null`，直接传入 `Url::parse(string $url)` 的类型声明会导致 `TypeError`。

```php
// App.php 第92行，建议改为：
$route = Request::server('PATH_INFO') ?? '';
```

### 1.3 `Config::get()` 使用 `require_once` 导致重复加载失败（严重）

**文件**: `src/Config.php` 第90行

`require_once` 在同进程内只执行一次。在测试或 Swoole 等长驻进程中，第二次加载同一配置文件返回 `true` 而非数组，`array_merge($config, true)` 将报错。

```php
// 建议改为 require + 类型检查
$append = require $cfg_file;
if (is_array($append)) {
    $config = array_merge($config, $append);
}
```

### 1.4 `Env::get()` 缺少键名保护

**文件**: `src/Env.php` 第61行

传入不存在的 `$key` 时直接访问 `self::$env[$key]` 产生 `Undefined index` 警告。

```php
return self::$env[$key] ?? null;
```

### 1.5 `Controller::validate()` 模块为 null 时路径异常

**文件**: `src/Controller.php` 第120行

`App::module()` 返回 `null` 时，拼接的路径含 `\\null\\`，验证器类名不匹配导致验证被跳过。

```php
// 当前
$path = '\\' . Env::appDir() . '\\' . App::module() . '\\' . ...;

// 建议增加空值保护
$module = App::module();
$path = '\\' . Env::appDir() . ($module ? '\\' . $module : '') . '\\' . ...;
```

### 1.6 注释与拼写错误

| 位置 | 问题 | 修复 |
|------|------|------|
| `src/App.php` 第146、154行 | 注释 `Cahce` | 改为 `Cache` |
| `src/App.php` 第154行 | 注释 "Log 使用 Db 处理器时的默认配置" 后面跟了 "Cahce" | 改为 `Log` |
| `src/Controller.php` 第21行 | `result()` 的 `$message` 注释为"错误信息" | 改为"提示信息" |
| `src/Controller.php` 第36行 | `success()` 的 `$message` 注释为"错误信息" | 改为"成功信息" |
| `src/Handler/ErrorHandler.php` 第35行 | 注释 `exit($errno)` 未清理 | 删除该行 |

---

## 二、架构优化

### 2.1 组件延迟加载

**现状**: `App::registerComponent()` 在构造时一次性初始化 Cookie、Request、Database、Cache、Log、Session、View 全部组件。

**问题**: 若某次请求只用到视图和日志，数据库/缓存/会话的初始化纯属浪费。

**建议**: 引入延迟代理（Lazy Proxy），各组件在首次被调用时才真正初始化：

```php
// 示例：延迟初始化数据库
public static function db(): Db
{
    static $db = null;
    if ($db === null) {
        $config = Config::get('database');
        $db = new Db($config['type'], $config['config'], $config['mode'] ?? null);
    }
    return $db;
}
```

### 2.2 引入中间件管道

**现状**: 框架生命周期为线性流程，没有扩展点。

**建议**: 在路由解析后、控制器执行前后增加中间件管道：

```php
// config/middleware.php
return [
    'global' => [
        \App\Middleware\CorsMiddleware::class,
    ],
    'route' => [
        'auth'  => \App\Middleware\AuthMiddleware::class,
        'admin' => \App\Middleware\AdminMiddleware::class,
    ],
];
```

适用场景：
- 全局 CORS 头处理
- 认证/鉴权拦截
- 请求速率限制
- 请求日志记录

### 2.3 减少全局静态依赖

**现状**: `Env`、`Config`、`Url`、`App` 等核心类全部使用 `static` 属性 + `static` 方法，导致：
- 类之间隐式耦合，难以独立测试
- 无法在同一进程中运行多个应用实例（如 Swoole 多协程）
- 测试间状态泄漏（一个测试修改的 Config 影响另一个测试）

**建议**: 将静态属性改为实例属性，通过单例或服务容器访问。短期可保留静态接口作为兼容层，内部委托给实例。

### 2.4 控制器方法参数注入增强

**现状**: `App::run()` 仅从 `Request::get()` 获取方法参数值。

**建议**: 通过反射识别类型声明，对对象类型参数从服务容器注入，对标量参数仍从请求获取：

```php
foreach ($ref_method->getParameters() as $parameter) {
    $type = $parameter->getType();
    if ($type && !$type->isBuiltin()) {
        // 对象类型 → 从容器注入
        $parameters[] = Container::get($type->getName());
    } else {
        // 标量类型 → 从请求获取
        $value = Request::get($parameter->getName());
        $parameters[] = $value ?? ($parameter->isOptional() ? $parameter->getDefaultValue() : throw ...);
    }
}
```

---

## 三、路由系统增强

### 3.1 支持 HTTP Method 路由

当前路由仅按 URL 模式匹配，不区分请求方法。建议扩展规则格式：

```php
return [
    'rules' => [
        'GET  /api/users'            => 'Api/User/index',
        'POST /api/users'            => 'Api/User/create',
        'PUT  /api/users/(?<id>\d+)' => 'Api/User/update',
    ]
];
```

### 3.2 命名路由

`Url::create()` 用 `array_search` 反查规则效率低且语义不清晰。建议支持命名路由：

```php
// 定义
'routes' => [
    'user.detail' => '/user/detail?id=<id>',
],

// 生成
Url::route('user.detail', ['id' => 42]);  // → /user/detail?id=42
```

### 3.3 `Url::create()` 正则反向解析缺陷

**文件**: `src/Url.php` 第137-148行

`preg_match` 匹配捕获组的正则 `#\(\?\<(?<name>[^\>]*)\>[^\)]*\)#` 无法处理嵌套括号的场景（如 `(?<id>\d{1,3}(?:px)?)`），会导致死循环或错误替换。建议改用括号计数或 token 解析。

---

## 四、安全增强

### 4.1 CSRF 防护

当前框架未提供 CSRF 防护。建议：
- 在 Session 中生成一次性 Token
- 表单辅助方法自动注入 `<input type="hidden" name="_token" value="...">`
- 在中间件中对 POST/PUT/DELETE 请求校验 Token

### 4.2 XSS 防御

`Controller::success()` / `Controller::error()` 中 `$message` 直接传入视图，若内容来自用户输入则存在 XSS 风险。建议在视图层默认启用 HTML 转义。

### 4.3 控制器操作访问控制

当前任何公开方法都可被路由直接调用，无保护机制。建议：
- 仅允许 `public` 且非 `__` 开头的方法被路由调用
- 支持控制器声明 `$allowActions` 白名单
- 支持 `$httpMethods` 限定请求方法，非法方法返回 405

```php
class UserController extends Controller
{
    protected $httpMethods = [
        'create' => ['POST'],
        'delete' => ['DELETE', 'POST'],
    ];
}
```

### 4.4 敏感信息泄露

`ExceptionHandler` 在非调试模式下仍输出完整异常堆栈到页面。建议区分环境：
- 调试模式：显示完整错误详情
- 生产模式：仅显示通用错误页面，详情只写入日志

---

## 五、性能优化

### 5.1 配置合并缓存

`Config::get()` 每次首次访问某配置文件都要读取并合并 4 层文件（框架 → 应用 → 公共模块 → 当前模块）。建议在 `runtime/` 下生成编译缓存：

```php
// runtime/config/app_Index.php — 合并后的完整配置
return ['version' => '1.0', 'debug' => false, ...];
```

通过比较源文件 `filemtime` 与缓存文件时间戳决定是否重新编译。

### 5.2 路由规则预编译

当路由规则较多时，每次请求都遍历正则匹配成本高。建议：
- 将路由规则编译为优化后的 PHP 数组缓存
- 支持按首字母/前缀分组，减少无效匹配

### 5.3 内置 PSR-4 自动加载

`examples/index.php` 和 `tests/TestApp.php`、`tests/TestController.php` 都手动注册 `spl_autoload_register`。建议框架在 `App::__construct()` 中根据 `Env::appPath()` 自动注册应用目录的 PSR-4 加载。

---

## 六、功能扩展

### 6.1 CLI 命令行模式

当前框架仅面向 HTTP。建议增加命令行入口：

```php
// console.php
$app = new App(['root_path' => __DIR__, 'mode' => 'cli']);
$app->command('migrate', MigrateCommand::class);
$app->runConsole($argv);
```

### 6.2 控制器生命周期钩子

在 `Controller` 基类中支持 `_before()` / `_after()` 方法，在每个操作执行前后自动调用：

```php
class BaseController extends Controller
{
    protected function _before()
    {
        // 通用的权限检查、数据预加载等
    }

    protected function _after()
    {
        // 统一的日志记录、资源清理等
    }
}
```

### 6.3 API 响应标准化

当前 `Controller::result()` 固定返回 HTTP 200 + `{code, message, data}`。建议：

- 支持传入 HTTP 状态码
- 提供 JSON:API 规范格式
- 错误响应包含 `errors` 数组而非单一 `message`

```php
protected function result(array $data, string $message = null, int $code = 0, int $httpStatus = 200)
```

### 6.4 分页工具类

提供内置分页组件，与数据库查询集成：

```php
$paginator = Paginator::create($query, $currentPage, $perPage);
// 返回 { data: [...], meta: { total, current_page, last_page, per_page } }
```

### 6.5 多语言支持（i18n）

当前异常消息、错误/成功页文案均为硬编码。建议引入语言包：

```php
// config/lang/zh_CN.php
return [
    'error.not_found' => '页面未找到',
    'error.server'    => '服务器内部错误',
];
```

### 6.6 `.env` 环境配置文件支持

当前环境配置仅通过 `App` 构造函数传入。建议支持 `.env` 文件：

```env
APP_DEBUG=true
DB_HOST=127.0.0.1
DB_NAME=myapp
CACHE_HANDLER=Redis
```

通过 `vlucas/phpdotenv` 或自研解析器加载，不同环境使用不同的 `.env` 文件。

---

## 七、测试改进

### 7.1 测试中状态隔离

**现状**: `TestApp`、`TestController` 在构造函数中创建 `App` 实例，多个测试共享静态状态，测试顺序依赖。

**建议**:
- 每个测试方法在 `setUp()` 中重置静态状态
- 为 `Config::$config`、`Env::$env` 等提供 `reset()` 方法
- 使用 `@runInSeparateProcess` 隔离无法重置的测试

### 7.2 补充边界用例测试

| 测试场景 | 说明 |
|---------|------|
| 空路由解析 | `PATH_INFO` 为空或 `/` 时路由正确回退到默认控制器 |
| 多级控制器 | URL 如 `/admin/sub/deep/action` 正确解析 |
| 模块关闭时验证器 | `module=false` 时 `Controller::validate()` 正常工作 |
| 配置深层合并 | 嵌套数组配置是否正确合并而非覆盖 |
| 路由捕获组边界 | 命名捕获组为空、含特殊字符时的行为 |
| `convertQuery` 边界 | 空字符串、缺少 `=`、值含 `=` 号的情况 |

### 7.3 测试目录规范

当前测试中引用 `dirname(__DIR__) . '/temp'` 目录，但该目录不在项目结构中。建议：
- 将测试 fixtures 统一放置在 `tests/fixtures/` 下
- 在 `phpunit.xml` 中配置 bootstrap 和环境变量

---

## 八、工程化

### 8.1 提升 PHP 最低版本要求

当前要求 `PHP >= 7.0.0`（2019年已EOL）。建议：
- 提升至 `PHP >= 7.4` 以使用箭头函数、属性类型声明、空合并赋值
- 或提升至 `PHP >= 8.0` 以使用联合类型、命名参数、`match` 表达式、Attributes

### 8.2 代码规范化配置

建议新增以下工程化配置文件：

| 文件 | 用途 |
|------|------|
| `.editorconfig` | 统一编辑器行为（缩进、编码、换行符） |
| `phpcs.xml` | PHP CodeSniffer 规则定义 |
| `phpstan.neon` | PHPStan 静态分析配置 |
| `phpunit.xml` | PHPUnit 测试配置（测试套件、覆盖率） |
| `.github/workflows/ci.yml` | GitHub Actions CI 流水线 |

### 8.3 `dirname(__FILE__, n)` 替换

多处使用 `dirname(__FILE__, 2)` 或 `dirname(__FILE__, 5)` 定位路径（如 `Env.php` 第46行、`Config.php` 第76行、`Controller.php` 第57行等）。建议统一替换为 `__DIR__` 常量，语义更清晰且性能更好：

```php
// 替换前
$appdir = dirname(__FILE__, 2) . '/app';

// 替换后
$appdir = __DIR__ . '/../../app';
// 或抽取为常量
const FRAMEWORK_ROOT = __DIR__ . '/..';
```

### 8.4 Composer 依赖版本策略

`composer.json` 中所有 `fize/*` 依赖使用 `"*"` 通配，生产环境可能因上游破坏性更新而崩溃。建议：
- 使用语义化版本约束，如 `"^1.0"` 或 `"~1.2"`
- 锁定 `composer.lock` 确保可复现构建
