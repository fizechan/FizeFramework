# FizeFramework 功能与优化建议

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

### 4.2 XSS 防御

`Controller::success()` / `Controller::error()` 中 `$message` 直接传入视图，若内容来自用户输入则存在 XSS 风险。建议在视图层默认启用 HTML 转义。

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

**现状**: `Root` / `Config` / `Url` 已无类级静态业务状态，测试可独立 `new`。剩余泄漏在 `App` 的 module/controller/action/`$instance` 等静态字段。

**建议**:
- 每个测试方法在 `setUp()` 中重置 `App` 静态状态，或每用例独立 `new App(...)`
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
