<?php

namespace Fize\Framework\Handler;

use Fize\Framework\App;
use Fize\Framework\Env;
use Fize\Log\Log;

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
        if (Env::get('debug')) {
            Log::info('耗时：' . App::timeTaken());
        }
    }
}
