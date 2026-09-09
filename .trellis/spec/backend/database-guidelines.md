# Database Guidelines

> 本仓库**没有**迁移系统、也没有自研 ORM。数据库是可选组件，通过 `fize/database` 的 `Fize\Database\Db` 接入。

## 何时会连库

`App::registerComponent()`：

```php
$db_config = $this->config->get('database');
if ($db_config) {
    new Db($db_config['type'], $db_config['config'], $db_config['mode'] ?? null);
}
```

框架默认 `app/config/database.php` 是空数组，空数组在 PHP 中为 falsy，**不会**初始化 Db。demo 的 `demo/config/database.php` 提供了 mysql/pdo 示例。

延迟访问：`App::db()` 读同一份配置，结果缓存在 `App::$lazyDb`。构造阶段已 `new Db` 时，组件库自身的静态/单例行为与 `App::db()` 是否指向同一对象，以 `fize/database` 为准；新代码优先 `App::db()` 或容器解析，不要再发明第三套入口。

## 配置形状

```php
return [
    'type'   => 'mysql',
    'mode'   => 'pdo',
    'config' => [
        'host'     => '...',
        'user'     => '...',
        'password' => '...',
        'dbname'   => '...',
    ],
];
```

应用代码用法见 `demo/app/Index/Controller/Index.php`：`Db::table('user')->limit(10)->select()`。这是 `fize/database` 的静态门面，属于该组件，不是本框架的 Env/Config/Url 模式。

## 其它组件复用同一份库配置

Cache / Log / Session 若 `handler` / `save_handler.type` 为 `DataBase`，且自身没写 `database`，`registerComponent()` 会把 `$db_config` 填进去。改默认值时三处逻辑要一起看（`src/App.php`）。

## 测试

单元测试不要用 demo 的真实 mysql。`tests/fixtures` 走框架空 `database.php`，避免 `new App` 连库。需要测 Db 时单独构造配置或 mock，不要在 bootstrap 测试里连 `localhost`。

## 本仓库不做的事

- 没有 `migrations/`、没有 schema 版本工具
- 没有表/列命名强制规范（那是应用库的事）
- 不要在本框架里引入 Eloquent / Doctrine，除非单独立项

## 反模式

- 在框架默认 `app/config/database.php` 写入真实账号
- 测试 `new App(['root_path' => demo])` 导致每次套件打 mysql（demo session 还可能是 DataBase）
- 为「规范好看」编造不存在的 Repository / Entity 层
