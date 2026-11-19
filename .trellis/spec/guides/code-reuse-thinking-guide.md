# 代码复用思考指南

> **目的**：创建新代码前先停下来想一想：类似实现是否已经存在？

---

## 问题所在

**重复代码是导致行为不一致的首要原因。**

复制粘贴或重写已有逻辑会导致：
- 缺陷修复无法同步到所有副本
- 各处行为随时间逐渐分化
- 代码库越来越难以理解

---

## 编写新代码前

### 第一步：先搜索

```bash
# 搜索相似的函数名
grep -r "functionName" .

# 搜索相似的逻辑
grep -r "keyword" .
```

### 第二步：回答以下问题

| 问题 | 如果是…… |
|----------|-----------|
| 是否已有类似函数？ | 复用或扩展该函数 |
| 其他位置是否使用了相同模式？ | 遵循现有模式 |
| 是否适合做成共享工具？ | 在合适的位置创建共享工具 |
| 是否在复制其他文件中的代码？ | **停止**，提取为共享实现 |

---

## 常见重复模式

### 模式一：复制粘贴函数

**不推荐**：将校验函数复制到另一个文件

**推荐**：提取为共享工具，并在需要处导入

### 模式二：相似组件

**不推荐**：创建一个与现有组件有 80% 相似度的新组件

**推荐**：通过 props / 变体扩展现有组件

### 模式三：重复常量

**不推荐**：在多个文件中定义相同常量

**推荐**：只保留一个事实来源，其他位置统一导入

### 模式四：重复提取载荷字段

**不推荐**：多个调用方在本地分别转换相同的 JSON / 事件字段：

```typescript
const description = (ev as { description?: string }).description;
const context = (ev as { context?: ContextEntry[] }).context;
```

即使只有两行代码，这仍然复制了契约逻辑。每个调用方都在自行定义有效载荷的含义。

**推荐**：将解码器、类型守卫或投影放在数据所有者旁边：

```typescript
if (isThreadEvent(ev)) {
  renderThreadEvent(ev);
}
```

**规则**：如果同一个未类型化载荷字段已在两个或更多位置读取，那么在增加第三个读取方之前，先创建共享的类型守卫、规范化器或投影。

---

## 何时提取抽象

**适合提取抽象的情况**：
- 相同代码出现 3 次或更多
- 逻辑复杂到容易引入缺陷
- 多人可能需要复用该逻辑

**不适合提取抽象的情况**：
- 只使用一次
- 简单的一行代码
- 抽象后的复杂度高于少量重复代码

---

## 批量修改后

对多个文件做相似修改后：

1. **复查**：是否覆盖了所有相关位置？
2. **搜索**：运行 grep 找出遗漏
3. **判断**：是否应该提取为共享抽象？

### Reducer 应使用穷尽式结构

当状态由类似动作的值（`action`、`kind`、`status`、`phase`）推导时，优先使用一个包含完整分支的 reducer `switch`，避免将状态更新散落在多个 `if/else` 中。

```typescript
// 不推荐 - 按动作分散状态转换，难以审查
if (action === "opened") { ... }
else if (action === "comment") { ... }
else if (action === "status") { ... }

// 推荐 - 由一个 reducer 统一管理状态转换表
switch (event.action) {
  case "opened":
    ...
    return;
  case "comment":
    ...
    return;
}
```

当事件日志是事实来源时，这一点尤其重要。reducer 定义了有据可循的状态重放模型；展示代码和命令不应各自复制其中一部分逻辑。

---

## 提交前检查清单

- [ ] 已搜索现有相似代码
- [ ] 没有应提取为共享实现的复制代码
- [ ] 未类型化载荷字段的提取逻辑统一由共享解码器负责
- [ ] 常量只在一个位置定义
- [ ] 相似模式遵循相同结构
- [ ] Reducer / 动作状态转换集中在一个 reducer 或命令分发器中

---

## 易错点：Python 的 if/elif/else 穷尽性检查

**问题**：Python 的 if/elif/else 链没有编译期穷尽性检查。向 `Literal` 类型（例如 `Platform`）添加新值后，已有分支链可能静默进入 `else` 并返回错误的默认值。

**表现**：新平台只能部分工作；某些方法返回 Claude 的默认值，而不是该平台专用值，且不会报错。

**Example** (`cli_adapter.py`):
```python
# 不推荐：「gemini」落入 else，错误地返回「claude」
@property
def cli_name(self) -> str:
    if self.platform == "opencode":
        return "opencode"
    else:
        return "claude"  # gemini 会静默得到 "claude"！

# 推荐：为每个平台显式编写分支
@property
def cli_name(self) -> str:
    if self.platform == "opencode":
        return "opencode"
    elif self.platform == "gemini":
        return "gemini"
    else:
        return "claude"
```

**预防方法**：向 Python `Literal` 类型添加新值时，搜索所有基于该类型分支的 if/elif/else 链，并为新值添加显式分支。不要假设新值进入 `else` 后就会得到正确结果。

---

## 易错点：不同机制需要生成相同结果

**问题**：两种不同机制需要生成相同文件集合时（例如 init 使用递归目录复制，而 update 手动调用 `files.set()`），重命名、移动或新增子目录等结构变更只会通过自动机制传播，手动维护的机制会逐渐偏离且不会报错。

**表现**：init 完全正常，但 update 会在错误路径创建文件，或完全漏掉某些文件。

**预防方法**：
- **优先做法**：消除两种机制的差异，让手动路径调用自动路径（例如让 `collectTemplateFiles()` 调用 `getAllScripts()`，不再各自维护文件清单）
- **无法消除差异时**：添加回归测试，对比两种机制的输出
- 迁移目录结构时，搜索所有引用旧结构的代码路径

**实际案例**：`trellis update` 为 11 个脚本手动维护了 `files.set()` 清单，而 `getAllScripts()` 已经跟踪这些脚本。修复时将手动清单替换为遍历 `getAllScripts()` 的 `for..of` 循环。参见 v0.4.0-beta.3 中对 `update.ts` 的重构。

---

## 模板文件注册（Trellis 专用）

向 `src/templates/trellis/scripts/` 添加新文件时：

**统一注册位置**：`src/templates/trellis/index.ts`

1. 添加 `export const xxxScript = readTemplate("scripts/path/file.py");`
2. 将其加入 `getAllScripts()` Map

完成注册即可。`commands/update.ts` 会直接使用 `getAllScripts()`，无需另外维护同步清单。

**原因**：如果没有在 `getAllScripts()` 中注册，`trellis update` 就不会将该文件同步到用户项目，缺陷修复和功能变更也无法传播。

**背景**：在 v0.4.0-beta.3 之前，`update.ts` 自行维护文件清单，经常与 `getAllScripts()` 不一致，导致 `trellis update` 静默漏掉 11 个 Python 文件。修复方式是移除重复清单，以 `getAllScripts()` 作为唯一事实来源。

### 新脚本快速检查清单

```bash
# 添加新的 .py 文件后，确认它已列入 getAllScripts()：
grep -l "newFileName" src/templates/trellis/index.ts  # Should match
```

### 模板同步约定

`.trellis/scripts/`（本仓库使用的版本）与 `packages/cli/src/templates/trellis/scripts/`（模板版本）必须保持一致。编辑 `.trellis/scripts/` 后，务必同步：

```bash
rsync -av --delete --exclude='__pycache__' .trellis/scripts/ packages/cli/src/templates/trellis/scripts/
```

**易错点**：rsync 的源路径和目标路径写错时，可能创建多层错误目录（例如 `.trellis/scripts/packages/cli/...`）。执行前务必再次核对路径。
