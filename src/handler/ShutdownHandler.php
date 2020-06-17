<?php


namespace fize\framework\handler;

use fize\framework\App;
use fize\log\Log;

/**
 * 结束处理器
 */
class ShutdownHandler implements ShutdownHandlerInterface
{
    /**
     * 执行
     */
    public function run()
    {
        if (App::env('debug')) {
            Log::info('耗时：' . App::timeTaken());
        }
    }
}
