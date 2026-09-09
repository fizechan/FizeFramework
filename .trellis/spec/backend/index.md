# Backend Development Guidelines

> FizeFramework（`fize/framework`）是 PHP MVC 框架源码仓库。本目录记录**当前代码里真实存在的写法**，供后续 AI 实现与检查时对齐。

单包、单层：`composer.json` 未配置多 package；`get_context.py --mode packages` 只报告 `backend`。

---

## Guidelines Index

| Guide | 何时必读 |
|-------|----------|
| [Directory Structure](./directory-structure.md) | 新增类、配置、应用控制器、测试 |
| [Bootstrap and Services](./bootstrap-and-services.md) | 改 `App` / `Env` / `Config` / `Url` / `Container`，或从控制器/Handler 取服务 |
| [Controllers and Routing](./controllers-and-routing.md) | 改路由、中间件、控制器基类或 action 注入 |
| [Error Handling](./error-handling.md) | 改 Handler、框架异常、成功/失败响应 |
| [Database Guidelines](./database-guidelines.md) | 改 `database` 配置、`App::db()`、Cache/Log/Session 的 DataBase 驱动 |
| [Logging Guidelines](./logging-guidelines.md) | 打日志或改 `log` 配置 |
| [Quality Guidelines](./quality-guidelines.md) | 写任何 PHP / 测试前 |

---

## Pre-Development Checklist

按改动范围阅读，不要只读本 index：

1. 始终阅读 [Quality Guidelines](./quality-guidelines.md)
2. 动目录或命名 → [Directory Structure](./directory-structure.md)
3. 动启动、配置、容器 → [Bootstrap and Services](./bootstrap-and-services.md)
4. 动控制器、URL、中间件 → [Controllers and Routing](./controllers-and-routing.md)
5. 动异常页或 Handler → [Error Handling](./error-handling.md)
6. 动数据库组件 → [Database Guidelines](./database-guidelines.md)
7. 动日志 → [Logging Guidelines](./logging-guidelines.md)
8. 再读 `.trellis/spec/guides/index.md`

---

## Quality Check

实现后对照：

- [ ] 没有恢复 `Env::` / `Config::` / `Url::` 静态业务状态
- [ ] 配置文件没有 `use Env` / `use App`，路径用 `%…%` 占位符
- [ ] 控制器经 `$this->app->env|config|url` 取服务，没有注入 `ContainerInterface`
- [ ] Handler 用 `App::getInstance()`，并容忍实例为 `null`
- [ ] 新测试自建实例 + `tests/fixtures/`，不依赖 gitignore 的 `temp/`
- [ ] `./vendor/bin/phpunit` 通过

---

**语言**：规范正文用中文；标识符、路径、类名保持与源码一致。
