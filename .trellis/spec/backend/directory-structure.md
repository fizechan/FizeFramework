# Directory Structure

> 本仓库是框架本身，不是普通业务应用。框架代码、默认配置、demo、测试要分开放。

## 顶层布局

```
src/                 # Fize\Framework\  PSR-4（composer.json autoload）
app/config/          # 框架默认配置（四层合并的最底层）
app/view/            # 框架内置 PHP 模板（404、success、error、handler）
demo/                # 示例应用：demo/app、demo/config、demo/public
tests/               # Tests\  PSR-4；夹具在 tests/fixtures/
docs/                # 人类文档（todo、改造说明），不是运行时输入
```

参考：`composer.json`、`README.md`、`demo/public/index.php`。

## 框架源码（`src/`）

| 路径 | 职责 |
|------|------|
| `src/App.php` | 组合根：构造 Env/Config/Url/Container，路由检查，`run()` |
| `src/Env.php` `src/Config.php` `src/Url.php` `src/Container.php` | 核心服务，纯实例 |
| `src/Controller.php` | 应用控制器基类 |
| `src/Handler/` | Error / Exception / Shutdown 及对应 Interface |
| `src/Middleware/` | `MiddlewareInterface`、`MiddlewarePipeline` |
| `src/Exception/` | 框架异常（模块/控制器/操作未找到、容器异常） |

新增框架类型：

- 核心类型：`src/Name.php`，命名空间 `Fize\Framework`
- 分层类型：`src/Layer/Name.php`，命名空间 `Fize\Framework\Layer`（现有 Handler、Middleware、Exception）

不要在 `src/` 里写应用业务控制器。

## 应用侧（demo 与使用本框架的项目）

入口构造 `App` 时传入 `root_path`（必填）。类路径由 Env 拼出来：

`\{ucfirst(app_dir)}\{Module}\{app_controller_dir}\{Controller}`

demo 默认：`App\Index\Controller\Index` → `demo/app/Index/Controller/Index.php`。

```
{root}/
├── app/{Module}/Controller/   # 控制器；可嵌套子目录（见 demo Admin、Tests/Subc）
├── app/{Module}/Validator/    # 与控制器同名的验证器（Controller::validate）
├── app/{Module}/View/         # 模块视图
├── config/*.php               # 应用配置 + config/common/ + config/{Module}/
├── public/index.php           # Web 入口（demo）
└── runtime/                   # 缓存、日志；由配置占位符指向
```

参考：`demo/public/index.php`、`App::checkController()`。

## 配置文件

每个主题一个 `return […]` 的 PHP 文件，文件名即 `Config::get()` 的第一段：

`app.php`、`url.php`、`controller.php`、`cookie.php`、`request.php`、`database.php`、`cache.php`、`log.php`、`session.php`、`view.php`、`handler.php`、`middleware.php`、`validator.php`

`url.php` 必须在确定模块之前可用，只放顶层，不要放到 `config/{Module}/`。见 `app/config/url.php` 注释与 `App::init()`。

## 测试

| 路径 | 约定 |
|------|------|
| `tests/TestXxx.php` | PHPUnit 类，命名空间 `Tests` |
| `tests/fixtures/` | 配置与最小应用夹具；`phpunit.xml` 排除此目录 |
| `phpunit.xml` | `suffix=".php"`（文件名是 `TestXxx.php` 不是 `XxxTest.php`） |

`composer.json` 的 `autoload-dev` 当前映射 `App\` → `demo/app/`、`Tests\` → `tests`。若夹具使用其它根命名空间（如 `Fixture\`），必须同时改 `autoload-dev`。

## 命名

- 类、方法、命名空间：PascalCase / camelCase，与现有 `App`、`checkModule` 一致
- 配置键、env 键：snake_case（`root_path`、`default_module`）
- 注释与用户文档：中文；标识符英文
- 不要引入与源码不符的目录名（例如把控制器目录写成小写 `controller`；Env 默认是 `Controller`）

## 反模式

- 在 `app/config/*.php` 里写应用业务（那是框架默认层）
- 把应用控制器放进 `src/`
- 测试去读不在仓库里的 `temp/`、`examples/`
- 用 `dirname(__FILE__, n)` 猜项目根；入口必须显式传 `root_path`（`Env::init()`）
