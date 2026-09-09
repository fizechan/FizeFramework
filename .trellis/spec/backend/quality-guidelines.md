# Quality Guidelines

> PHP `>= 7.2`。能用 `void`、可空类型 `?T`、`object`；**不要**用属性类型、箭头函数、`match`、联合类型、`mixed` 返回（那是 7.4 / 8.0+）。`psr/container` 锁在 `^1.1`，不要升 `^2.0`。

## 必须遵守

- **Env / Config / Url 保持实例**。服务从 `App` 或容器取。见 [bootstrap-and-services.md](./bootstrap-and-services.md)
- **配置是数据**。`app/config` 与应用 `config` 只 `return` 数组 + `%placeholder%`
- **中文注释、英文标识符**，与现有 `src/` 一致
- **构造注入或 `App` 定位**，不要给业务对象塞容器
- 改行为时补 `tests/Test*.php`，夹具放 `tests/fixtures/`

## 禁止

| 禁止 | 原因 |
|------|------|
| 给 Env/Config/Url 加回 `static` 属性当全局状态 | 测试泄漏、多实例互盖 |
| `dirname(__FILE__, 5)` 推断 `root_path` | 布局一变即错；现已强制传入 |
| 浅合并配置（`array_merge` 整段换掉嵌套数组） | `Config` 已用 `array_replace_recursive` |
| 在未立项时改 `Url::parse` 的 `$_GET` 副作用 | 路由语义，不是顺手重构 |
| 对 `fize/*` 依赖写死通配以外的「随便升级」而不跑测试 | `composer.json` 目前仍是 `"*"`，改约束要单独立项 |
| 把 demo 的 mysql / DataBase session 当默认测试环境 | 套件会连真实库 |

## 测试

- 运行：`./vendor/bin/phpunit`（`phpunit.xml`）
- 文件名 `TestXxx.php`；目录扫描 `suffix=".php"`，排除 `tests/fixtures`
- 每个用例自己 `new Env/Config/Url/App`，用断言证明两实例互不影响
- 构造 `App` 的用例：`setUp` 记 `ob_get_level()`，清空 `$_GET`；`tearDown` 只关掉多出来的缓冲并 `restore_*_handler()`（`tests/TestApp.php`）
- `_r` 带前导 `/`
- 不要 `var_dump` 充当断言（旧测试已清掉）

## 代码风格（现状，不是新工具链）

仓库**没有** `phpcs.xml` / `phpstan.neon` / `.editorconfig`（`docs/todo.md` 8.2 待做）。当前对齐方式：

- 四空格缩进
- 类/方法 PHPDoc（`@param` / `@return` / `@var`）
- 框架异常与公开 API 保持现有方法名，避免为「更现代」改名（例如不要把 `Config::get` 改成 `read`）

## 审查清单

- [ ] 新公共方法有 PHPDoc，且 PHP 7.2 能解析
- [ ] 没有新增类级静态业务状态
- [ ] 配置/路径不依赖未初始化的 App
- [ ] 与本次无关的 todo（CSRF、命名路由、dotenv）没有被顺手实现
- [ ] PHPUnit 全绿

## 已知债务（写进代码前先看 todo，不要当既成规范去模仿）

- `App` 仍有 `$module` / `$controller` / `$action` / `$class` 静态字段
- `Url::$config['maps']` 未使用
- Handler 内置页在非 debug 仍可能暴露细节
- 组件（Cache/Log/Cookie 等）仍是各自包的静态用法
