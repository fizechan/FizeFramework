# Config / Env / Url：PSR-11 容器 + 纯实例（已实施）

将 `Config`、`Env`、`Url` 从类级静态状态改为纯实例，由 `App` 组合根创建并注册到最小 PSR-11 容器。不保留静态门面。

## 访问约定

- `App` 持有 `public $env` / `$config` / `$url`，与容器内为同一批对象
- `App::getInstance()` 返回当前应用（构造时注册，未构造为 `null`）
- 类外：`$app->env->get()`、`App::getInstance()->config->get()`、`$app->container()->get(Config::class)`
- `Controller` 持有 `protected $app`，类内用 `$this->app->env` 等；由 `App::run()` 调用 `bindApp()`
- `Handler` 使用 `App::getInstance()`
- 配置文件为纯数据，使用 `%runtime_path%`、`%module_path%` 等占位符，不再调用 `Env::` / `App::`

## 容器

- `src/Container/Container.php` 实现 `Psr\Container\ContainerInterface`（`psr/container: ^1.1`）
- `get` / `has` 符合 PSR-11；未知 id 抛 `NotFoundExceptionInterface`
- `set` 注册共享实例；`make` 按构造类型提示装配且不缓存

## Env / Config / Url

- `Env`：`root_path` 必填；`parameters()` 提供插值映射
- `Config`：四层加载 + `array_replace_recursive` + `%name%` 插值；`setModule()` 不清已缓存文件
- `Url`：实例方法 `parse()` / `create()`，`parse()` 仍写入 `$_GET`

## 测试隔离

`Env` / `Config` / `Url` 无类级静态业务状态。`App` 上仍有 module/controller/action 等静态字段，完整隔离见 todo 7.1。
