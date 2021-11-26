<?php


namespace Fize\Framework\Handler;

/**
 * 错误处理器接口
 */
interface ErrorHandlerInterface
{

    /**
     * 执行
     * @param int    $errno   错误级别
     * @param string $errstr  错误信息
     * @param string $errfile 发生错误的文件名
     * @param int    $errline 发生错误的行号
     * @return bool 是否不触发系统默认错误处理器
     */
    public function run($errno, $errstr, $errfile = null, $errline = 0);
}
