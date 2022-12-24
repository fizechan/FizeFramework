<?php

use Fize\Framework\Handler\ErrorHandler;
use Fize\Framework\Handler\ExceptionHandler;
use Fize\Framework\Handler\ShutdownHandler;

/**
 * 处理器设置
 */
return [
    'error'     => ErrorHandler::class,
    'exception' => ExceptionHandler::class,
    'shutdown'  => ShutdownHandler::class,
];
