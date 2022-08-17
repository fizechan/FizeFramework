<?php

namespace Fize\Framework\Handler;

use Fize\Framework\Exception\NotFoundException;
use Fize\Framework\Exception\ResponseException;
use Fize\Framework\HandlerInterface\ExceptionHandlerInterface;
use Fize\Log\Log;
use Fize\View\ViewFactory;
use Fize\Web\Response;
use Throwable;

/**
 * 异常处理器
 */
class ExceptionHandler implements ExceptionHandlerInterface
{

    /**
     * 执行
     * @param Throwable $exception 异常
     */
    public function run(Throwable $exception)
    {
        if ($exception instanceof ResponseException) {
            $response = $exception->getResponse();
            $response->send();
        } elseif ($exception instanceof NotFoundException) {
            Log::notice("[404]Not Found[{$exception->getMessage()}] : {$exception->url()}");
            $view = ViewFactory::create('Php', ['view' => dirname(__DIR__) . '/view']);
            $view->assign('exception', $exception);
            $response = Response::html($view->render('404'));
            $response->withStatus(404)->send();
        } else {
            Log::error("[{$exception->getCode()}]{$exception->getMessage()} : {$exception->getFile()} Line: {$exception->getLine()}");
            $view = ViewFactory::create('Php', ['view' => dirname(__DIR__) . '/view']);
            $view->assign('exception', $exception);
            $response = Response::html($view->render('exception_handler'));
            $response->withStatus(500)->send();
        }
    }
}
