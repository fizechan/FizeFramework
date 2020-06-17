<?php

use fize\framework\handler\ErrorHandler;
use fize\framework\handler\ExceptionHandler;
use fize\framework\handler\ShutdownHandler;

/**
 * 处理器设置
 */
return [
    'error'     => ErrorHandler::class,
    'exception' => ExceptionHandler::class,
    'shutdown'  => ShutdownHandler::class,
];
