# FizeFramework

Fize出品的WEB开发框架 —— 一个轻巧、自由、灵活的 PHP MVC 框架。

## 特性

- **MVC 架构**：清晰的模块(Module) → 控制器(Controller) → 操作(Action) 三层结构
- **多模块支持**：支持按模块分组管理业务，也可关闭模块直接使用
- **灵活的路由**：支持 URL 路由规则配置、正则匹配与命名捕获组
- **多模板引擎**：默认使用 PHP 原生模板，同时支持 Twig 等第三方引擎
- **统一配置管理**：框架默认配置 → 应用配置 → 公共模块配置 → 当前模块配置，逐层合并覆盖
- **完善的异常处理**：内置错误(Error)、异常(Exception)、终止(Shutdown)三大处理器，均可自定义替换
- **内置组件集成**：开箱集成缓存(Cache)、数据库(Database)、日志(Log)、会话(Session)、Cookie、视图(View)等组件
- **数据验证器**：支持按控制器/操作自动匹配验证规则，支持场景验证
- **自动参数绑定**：控制器方法参数从请求中自动注入
- **响应自动识别**：控制器返回 `Response` 对象、字符串或数组时自动选择对应的响应方式（HTML/JSON）

## 环境要求

- PHP >= 7.0.0
- Composer

## 安装

通过 Composer 安装：

```bash
composer require fize/framework
```

## 项目结构

```
project/
├── app/                    # 应用目录
│   ├── Index/              # 模块（分组）
│   │   ├── Controller/     # 控制器目录
│   │   │   └── Index.php
│   │   ├── Validator/      # 验证器目录
│   │   └── View/           # 视图目录
│   └── ...
├── config/                 # 配置目录
│   ├── app.php             # 应用配置
│   ├── cache.php           # 缓存配置
│   ├── controller.php      # 控制器配置
│   ├── cookie.php          # Cookie 配置
│   ├── database.php        # 数据库配置
│   ├── handler.php         # 处理器配置
│   ├── log.php             # 日志配置
│   ├── request.php         # 请求配置
│   ├── session.php         # 会话配置
│   ├── url.php             # URL 路由配置
│   ├── validator.php       # 验证器配置
│   └── view.php            # 视图配置
├── runtime/                # 运行时目录（缓存、日志等）
├── index.php               # 入口文件
└── composer.json
```

## 快速开始

### 1. 创建入口文件 `index.php`

```php
<?php

require __DIR__ . '/vendor/autoload.php';

$app = new \Fize\Framework\App([
    'root_path' => __DIR__,
    'debug'     => true,  // 开发环境开启调试
]);
$app->run();
```

### 2. 创建控制器

在 `app/Index/Controller/` 下创建 `Index.php`：

```php
<?php

namespace App\Index\Controller;

use Fize\Framework\Controller;
use Fize\View\View;

class Index extends Controller
{
    public function index()
    {
        View::assign('title', 'Hello FizeFramework');
        return View::render();
    }
}
```

### 3. 配置 Web 服务器

将 Web 服务器的根目录指向项目根目录，并确保所有请求转发到 `index.php`。

Apache 示例（`.htaccess`）：
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

## 路由规则

URL 格式为：`/模块/控制器/操作`，路由解析流程：

1. 从 `PATH_INFO` 或 GET 参数 `_r` 获取路由
2. 匹配 `config/url.php` 中定义的路由规则（支持正则）
3. 解析出模块、控制器和操作

### 配置路由规则

在 `config/url.php` 中添加：

```php
return [
    'rules' => [
        '/article/(?<id>\d+)' => '/Index/Article/detail?id=<id>',
    ]
];
```

## 配置系统

配置采用分层合并策略，优先级从低到高：

1. **框架默认配置**（`vendor/fize/framework/app/config/`）
2. **应用配置**（`config/*.php`）
3. **公共模块配置**（`config/common/*.php`）
4. **当前模块配置**（`config/{module}/*.php`）

支持以 `.` 分隔的层级键名访问配置：

```php
use Fize\Framework\Config;

$version = Config::get('app.version');
$defaultController = Config::get('controller.default_controller');
```

### 环境参数（Env）

在创建 `App` 实例时传入：

| 参数 | 说明 | 默认值 |
|------|------|--------|
| `root_path` | 项目根目录 | 自动推断 |
| `app_dir` | 应用文件夹 | `app` |
| `config_dir` | 配置文件夹 | `config` |
| `runtime_dir` | 运行时文件夹 | `runtime` |
| `app_controller_dir` | 控制器文件夹 | `controller` |
| `app_view_dir` | 视图文件夹 | `view` |
| `module` | 模块设置：`true` 开启并自动判断，`false` 关闭，字符串指定模块 | `true` |
| `default_module` | 默认模块 | `index` |
| `route_key` | 兼容模式路由 GET 参数名 | `_r` |
| `debug` | 是否调试模式 | `false` |

## 控制器

控制器继承 `Fize\Framework\Controller`，提供以下内置方法：

```php
class MyController extends Controller
{
    // 返回 JSON 数据
    $this->result(['key' => 'value'], 'success');

    // 成功提示（AJAX 返回 JSON，普通请求渲染成功页）
    $this->success('操作成功', '/redirect/url');

    // 失败提示（AJAX 返回 JSON，普通请求渲染错误页）
    $this->error('操作失败');

    // 页面跳转
    $this->redirect('/target/url', ['param' => 'value']);

    // 数据验证（自动匹配对应的 Validator 类）
    $this->validate($data);
}
```

### 自动参数绑定

控制器方法的参数会自动从 GET 请求中注入：

```php
public function detail($id, $page = 1)
{
    // $id 从请求参数中获取，必填
    // $page 从请求参数中获取，缺省为 1
}
```

## 数据验证

验证器自动按 `模块/Validator/控制器名` 路径查找，支持场景（对应操作名）：

```php
namespace App\Index\Validator;

use Fize\Security\Validator;

class Test extends Validator
{
    protected $rules = [
        'name' => 'required|minLength:2',
    ];

    protected $scenes = [
        'create' => ['name'],
    ];
}
```

## 异常处理

框架内置三大处理器，均可通过 `config/handler.php` 替换为自定义实现：

- **ErrorHandler**：处理 PHP 错误（`set_error_handler`）
- **ExceptionHandler**：处理异常，区分 `HttpResponseException`（正常响应）、`NotFoundException`（404）和其他异常（500）
- **ShutdownHandler**：脚本结束时执行，调试模式下记录执行耗时

自定义处理器只需实现对应接口（`ErrorHandlerInterface`、`ExceptionHandlerInterface`、`ShutdownHandlerInterface`），然后在配置中指定类名即可。

## 内置组件

| 组件 | 包名 | 说明 |
|------|------|------|
| 缓存 | `fize/cache` | 支持文件、数据库等多种缓存驱动 |
| 数据库 | `fize/database` | 数据库操作封装 |
| 日志 | `fize/log` | 支持文件、数据库等日志驱动 |
| 视图 | `fize/view` | 支持 PHP 原生模板、Twig 等引擎 |
| Web | `fize/web` | Request、Response、Cookie、Session 封装 |
| 安全 | `fize/security` | 数据验证等安全工具 |
| IO | `fize/io` | 文件与目录操作 |
| 异常 | `fize/exception` | HTTP 异常等 |

## 许可证

MIT License
