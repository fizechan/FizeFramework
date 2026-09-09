# Bootstrap and Services

> `App` 是组合根。`Env` / `Config` / `Url` 是普通对象，由 `App` 持有并 `set` 进最小 PSR-11 容器。不要把它们写回类级 `static` 状态。

## 启动顺序

`new App($env)`（`src/App.php`）固定顺序：

1. `self::$instance = $this`
2. `init()`：Container → Env → Config → Url → `checkModule()` → `Config::setModule()`
3. `setHandler()`：用当前 `$app` 闭包注册 error / exception / shutdown
4. `registerComponent()`：Cookie、Request、可选 Db、Cache、Log、Session、条件 View
5. `check()`：解析 controller / action

入口实例：`demo/public/index.php`。

```php
$app = new App([
    'root_path' => dirname(__FILE__, 2),
    'debug'     => true,
]);
$app->run();
```

## Env

类：`src/Env.php`。

- `root_path` 必填，缺省或空字符串抛 `InvalidArgumentException`
- 其余键有默认值：`app_dir=app`、`config_dir=config`、`runtime_dir=runtime`、`app_controller_dir=Controller`、`app_view_dir=View`、`module=true`、`default_module=Index`、`route_key=_r`、`debug=false`
- 路径助手：`rootPath()` / `appPath()` / `configPath()` / `runtimePath()`
- `parameters($module)` 返回 Config 插值表：`%root_path%`、`%app_path%`、`%config_path%`、`%runtime_path%`、`%app_dir%`、`%app_view_dir%`、`%app_controller_dir%`、`%module%`、`%module_path%`
- 无模块时 `%module_path%` 等于 `%app_path%`

测试：`tests/TestEnv.php`。

## Config

类：`src/Config.php`。

加载顺序（后者覆盖前者，`array_replace_recursive`）：

1. `app/config/{file}.php`（框架默认）
2. `{config_dir}/{file}.php`
3. `{config_dir}/common/{file}.php`
4. `{config_dir}/{module}/{file}.php`（仅已 `setModule`）

规则：

- `get('file.key')` 第一段是文件名
- 加载后对字符串值做 `strtr` 占位符替换
- 配置文件必须是纯数组，禁止 `use Env` / `use App`
- `setModule()` 只更新 `$module` 与 `%module%` / `%module_path%`，**不清**已缓存文件
- `url` 在 `setModule` 之前读取，之后不要依赖模块层覆盖 url

占位符示例（`app/config/cache.php`、`app/config/view.php`、`demo/config/view.php`）：

```php
'path'  => '%runtime_path%/cache',
'view'  => '%module_path%/%app_view_dir%',
```

`date('Ymd')` 这类求值可以留在配置文件里（`app/config/log.php`），只要不调用 Env/App。

测试：`tests/TestConfig.php`，夹具 `tests/fixtures/config/`。

## Url

类：`src/Url.php`。实例方法 `parse()` / `create()`。`parse()` 仍会写入 `$_GET`（当前路由语义，不要在未立项的情况下改掉）。

测试：`tests/TestUrl.php`（直接 `new Url($rules)`）。

## 容器

类：`src/Container.php`，实现 `Psr\Container\ContainerInterface`（`psr/container: ^1.1`）。

| 方法 | 含义 |
|------|------|
| `get` / `has` | PSR-11；未知 id 抛 `ContainerNotFoundException` |
| `set($id, $entry)` | 注册共享实例 |
| `make($class)` | 按构造类型提示装配，**不缓存**（Controller / Handler 每次新建） |

`App::init()` 注册：`ContainerInterface`、`Container`、`App`、`Env`、`Config`、`Url`。同一 id 两次 `get` 是同一对象。

`resolveFromContainer()` 先 `has`/`get`，再特判 `Request`，再尝试无参可实例化类。

不要把 `ContainerInterface` 注入 Controller 再自行 `get`。容器只在 `App` 里用。

测试：`tests/TestContainer.php`。

## 谁持有什么、怎么取

| 调用方 | 取法 |
|--------|------|
| `App` 类内 | `$this->env` / `$this->config` / `$this->url`（均为 `public`） |
| 入口、测试 | `$app->env->get()` 或 `$app->container()->get(Env::class)`，二者同一实例 |
| 任意外部 | `App::getInstance()`（未构造为 `null`） |
| `Controller` | `protected $app`；`App::run()` 在 `make()` 之后 `bindApp($this)`；类内 `$this->app->config` |
| Handler | `App::getInstance()`，见 `src/Handler/ShutdownHandler.php` |

demo：`demo/app/Index/Controller/Index.php` 用 `$this->app->config->get('app.version')`。

## 反模式

- `Env::get()` / `Config::get()` / `Url::parse()` 静态门面（已删除，不要加回来）
- 构造 Env/Config/Url 时写入 `static` 属性（测试会互相污染）
- 配置文件 `require` 时调用 `Env::runtimePath()`
- 在 Controller 上再声明 `$env` / `$config` / `$url`
- 假设 `dirname(__FILE__, 5)` 能推出项目根
