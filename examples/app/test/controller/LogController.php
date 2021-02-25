<?php


namespace app\test\controller;

use Psr\Log\LogLevel;
use fize\log\Log;


class LogController
{

    public function info()
    {
        Log::info('写点东西啦');
    }

    public function notice()
    {
        Log::notice('写点东西啦');
    }

    public function alert()
    {
        Log::alert('写点东西啦');
    }

    public function debug()
    {
        Log::debug('写点东西啦');
    }

    public function emergency()
    {
        Log::emergency('写点东西啦');
    }

    public function error()
    {
        Log::error('写点东西啦');
    }

    public function warning()
    {
        Log::warning('写点东西啦');
    }

    public function critical()
    {
        Log::critical('写点东西啦');
    }

    public function log()
    {
        Log::log(LogLevel::ERROR, '写点东西啦');
    }
}