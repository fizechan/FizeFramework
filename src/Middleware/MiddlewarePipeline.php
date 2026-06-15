<?php

namespace Fize\Framework\Middleware;

use Fize\Web\Request;
use Fize\Web\Response;

/**
 * 中间件管道
 *
 * 按顺序执行中间件链，最终调用核心处理器。
 */
class MiddlewarePipeline
{

    /**
     * @var MiddlewareInterface[] 中间件实例列表
     */
    protected $middlewares = [];

    /**
     * 添加中间件
     * @param MiddlewareInterface $middleware 中间件实例
     * @return self
     */
    public function pipe(MiddlewareInterface $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * 执行管道
     * @param Request  $request 请求对象
     * @param callable $core    核心处理器（通常是控制器执行逻辑）
     * @return Response|null
     */
    public function process(Request $request, callable $core)
    {
        $pipeline = array_reduce(
            array_reverse($this->middlewares),
            function ($next, MiddlewareInterface $middleware) {
                return function ($request) use ($middleware, $next) {
                    return $middleware->handle($request, $next);
                };
            },
            $core
        );

        return $pipeline($request);
    }
}
