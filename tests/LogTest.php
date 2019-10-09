<?php

use PHPUnit\Framework\TestCase;
use fize\framework\Log;

class LogTest extends TestCase
{

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $config = [
            'handler' => 'File',
            'config'  => [
                'path'     => __DIR__ . '/runtime/log',
                'file'     => date('Ymd') . '.log',
                'max_size' => 2 * 1024 * 1024
            ]
        ];
        new Log($config);
    }

    public function testInfo()
    {
        Log::info('写点东西啦');
        self::assertTrue(true);
    }

    public function testNotice()
    {
        Log::notice('写点东西啦');
        self::assertTrue(true);
    }

    public function testAlert()
    {
        Log::alert('写点东西啦');
        self::assertTrue(true);
    }

    public function testDebug()
    {
        Log::debug('写点东西啦');
        self::assertTrue(true);
    }

    public function testEmergency()
    {
        Log::emergency('写点东西啦');
        self::assertTrue(true);
    }

    public function testError()
    {
        Log::error('写点东西啦');
        self::assertTrue(true);
    }

    public function testWarning()
    {
        Log::warning('写点东西啦');
        self::assertTrue(true);
    }

    public function testCritical()
    {
        Log::critical('写点东西啦');
        self::assertTrue(true);
    }

    public function testLog()
    {
        Log::log(Log::LEVEL_ERROR, '写点东西啦');
        self::assertTrue(true);
    }
}
