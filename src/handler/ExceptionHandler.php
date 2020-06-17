<?php


namespace fize\framework\handler;

use Throwable;
use fize\framework\exception\NotFoundException;
use fize\framework\exception\ResponseException;
use fize\log\Log;
use fize\view\ViewFactory;
use fize\web\Response;


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
