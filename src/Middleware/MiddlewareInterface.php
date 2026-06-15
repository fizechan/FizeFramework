<?php

namespace Fize\Framework\Middleware;

use Fize\Web\Request;
use Fize\Web\Response;

/**
 * 中间件接口
 *
 * 实现此接口以创建自定义中间件，用于在控制器执行前后进行处理。
 */
interface MiddlewareInterface
{

    /**
     * 处理请求
     * @param Request  $request 请求对象
     * @param callable $next    下一个中间件/处理器
     * @return Response|null 返回 Response 可中断管道，返回 null 则继续执行
     */
    public function handle(Request $request, callable $next);
}
