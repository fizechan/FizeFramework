<?php


namespace Fize\Framework\Handler;

use Fize\Log\Log;
use Fize\View\ViewFactory;
use Fize\Web\Response;

/**
 * 错误处理器
 */
class ErrorHandler implements ErrorHandlerInterface
{

    /**
     * 执行
     * @param int         $errno   错误级别
     * @param string      $errstr  错误信息
     * @param string|null $errfile 发生错误的文件名
     * @param int         $errline 发生错误的行号
     * @return bool 返回true表示不触发系统默认错误处理器
     */
    public function run(int $errno, string $errstr, string $errfile = null, int $errline = 0): bool
    {
        Log::error("[$errno]$errstr : $errfile Line: $errline");
        $appdir = dirname(__FILE__, 2) . '/app';
        $view = ViewFactory::create('Php', ['view' => $appdir . '/view']);
        $view->assign('errno', $errno);
        $view->assign('errstr', $errstr);
        $view->assign('errfile', $errfile);
        $view->assign('errline', $errline);
        $response = Response::html($view->render('error_handler'));
        $response->withStatus(500)->send();
//        exit($errno);
        return false;
    }
}
