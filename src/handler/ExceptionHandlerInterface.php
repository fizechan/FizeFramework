<?php


namespace fize\framework\handler;

use Throwable;

/**
 * 异常处理器
 */
interface ExceptionHandlerInterface
{

    /**
     * 执行
     * @param Throwable $exception 异常
     */
    public function run(Throwable $exception);
}
