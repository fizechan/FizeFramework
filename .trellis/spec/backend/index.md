# 后端开发规范

> FizeFramework（`fize/framework`）是 PHP MVC 框架源码仓库。本目录记录**当前代码里真实存在的写法**，供后续 AI 实现与检查时对齐。

单包、单层：`composer.json` 未配置多 package；`get_context.py --mode packages` 只报告 `backend`。

---

## 规范索引

| 规范 | 何时必读 |
|-------|----------|
| [目录结构](./directory-structure.md) | 新增类、配置、应用控制器或测试 |
| [启动流程与服务](./bootstrap-and-services.md) | 修改 `App` / `Env` / `Config` / `Url` / `Container`，或从控制器 / Handler 获取服务 |
| [控制器与路由](./controllers-and-routing.md) | 修改路由、中间件、控制器基类或 action 注入 |
| [错误处理](./error-handling.md) | 修改 Handler、框架异常或成功 / 失败响应 |
| [数据库规范](./database-guidelines.md) | 修改 `database` 配置、`App::db()` 或 Cache / Log / Session 的 DataBase 驱动 |
| [日志规范](./logging-guidelines.md) | 记录日志或修改 `log` 配置 |
| [质量规范](./quality-guidelines.md) | 编写任何 PHP 代码或测试前 |

---

## 开发前检查清单

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

## 质量检查

实现后对照：

- [ ] 没有恢复 `Env::` / `Config::` / `Url::` 静态业务状态
- [ ] 配置文件没有 `use Env` / `use App`，路径用 `%…%` 占位符
- [ ] 控制器经 `$this->app->env|config|url` 取服务，没有注入 `ContainerInterface`
- [ ] Handler 用 `App::getInstance()`，并容忍实例为 `null`
- [ ] 新测试自建实例 + `tests/fixtures/`，不依赖 gitignore 的 `temp/`
- [ ] `./vendor/bin/phpunit` 通过

---

**语言**：
- 规范正文用中文；标识符、路径、类名保持与源码一致。
- 如无特别说明，总体使用PSR-12编码规范。
- 行长度不做实际限制，只有当方法调用时多个参数导致行长度超过200个字符时才将参数进行换行，每行参数个数不超过5个。
- 使用`[]`方括号语法定义索引数组，而非`array()`。
