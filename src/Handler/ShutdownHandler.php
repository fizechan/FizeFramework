<?php

namespace Fize\Framework\Handler;

use Fize\Framework\App;
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
        $app = App::getInstance();
        $debug = $app ? $app->env->get('debug') : false;
        if ($debug) {
            Log::info('耗时：' . App::timeTaken());
        }
    }
}
