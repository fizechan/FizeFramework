# Controllers and Routing

> 路由是「PATH_INFO 或 `_r` → `Url::parse` → 模块/控制器/操作」。控制器继承 `Fize\Framework\Controller`，经 `$this->app` 访问服务。

## 路由输入

`App::getRoute()`（实例方法，结果缓存在 `$this->route`）：

1. 若 `$_GET[$env.route_key]` 存在（默认 `_r`），用该值
2. 否则 `PATH_INFO`，缺省 `''`
3. `$this->url->parse($route)`
4. 去掉首尾 `/`

因此兼容参数应带前导斜杠：`$_GET['_r'] = '/index/test'`。写成 `index/test` 会被当成去掉首字符，解析成错误类名。见 `tests/TestApp.php`。

模块（`App::checkModule()`）：

- `module === false`：无分组，`App::module()` 为 `null`
- `module === true`：第一段路径是模块；空路由用 `default_module`（默认 `Index`）
- `module` 为字符串：固定模块

控制器解析（`App::check()`）：剩余段按 0/1/多段变成 default controller、单控制器、或 `子目录\Controller` + action。先试带 `controller_postfix`（默认 `Controller`）的类名，再试无后缀。参考 `src/App.php` 的 `checkController()`。

## URL 规则

`config/url.php`：

```php
return [
    'maps'  => [],   // 已定义，Url 当前未读取
    'rules' => [
        '/news/(?<id>\d+)' => '/index/news/details',
    ],
];
```

- `parse`：按 `rules` 从上到下 `preg_match`；命名捕获写入 `$_GET` 或替换 `<name>`
- `create`：`array_search` 反查 `rules` 的值，再填捕获组
- 未匹配则原样返回（`create` 仍会 `appendQuery`）

demo 规则：`demo/config/url.php`。嵌套括号捕获的反向替换有已知缺陷（`docs/todo.md` 3.3），未立项不要顺手改。

## 控制器

基类：`src/Controller.php`。

- `protected $app`，外部不读 `$controller->app`
- `bindApp(App $app)` 由 `App::run()` 在 `container->make($class)` 之后调用
- 用户控制器可以有自己的无参 `__construct()`（如 `demo/app/Index/Controller/Test.php`），不调用 `parent::__construct` 也可以，因为绑定发生在构造之后

类内：

```php
$this->app->config->get('view');
$this->app->env->appDir();
$this->app->url->create($url, $params);
```

内置动作（都通过抛 `HttpResponseException` 离开）：

| 方法 | 行为 |
|------|------|
| `result()` | JSON `{code, message, data}` |
| `success()` / `error()` | AJAX 走 JSON；否则看 view 配置模板，否则框架 `app/view` |
| `redirect()` | `$this->app->url->create()` 后 `Response::redirect` |
| `validate()` | 按模块/common 查找 Validator，场景名是当前 action |

不继承 `Controller` 的类（改造前 `demo/app/Admin/Controller/Test2.php` 曾如此）没有 `$this->app`。需要服务时让它 `extends Controller`，或用 `App::getInstance()`。

## Action 参数注入

`App::resolveParameters()`：

- 非内置类型：`resolveFromContainer()`（容器已有则注入，否则无参类或失败）
- 标量：`Request::get($name)`；无值且无默认抛 `ParameterNotSetException`

因此 `public function index(Config $config)` 合法；`public function param($id)` 从查询串取。见 `demo/app/Tests/Controller/Index.php`。

## 中间件

`app/config/middleware.php`：`global` 在 `App::loadMiddleware()` 里对每个请求执行；`route` 键已出现在配置注释中，管道当前只挂 `global`。

实现 `Fize\Framework\Middleware\MiddlewareInterface::handle(Request $request, callable $next)`。返回 `Response` 中断；返回 `null` 继续。`App::run()` 把控制器执行包进 pipeline。

## 反模式

- 控制器里 `Config::get` / `Url::create`
- 把容器塞进控制器当 Service Locator
- 测试构造 `App` 前不清空 `$_GET['_r']`（会串路由）
- 假定 `maps` 已生效
- 新增全局中间件却不实现 `MiddlewareInterface`（会被跳过）
