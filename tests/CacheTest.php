<?php


use fize\framework\Cache;
use PHPUnit\Framework\TestCase;

class CacheTest extends TestCase
{

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $config = [
            'handler' => 'File',
            'config'  => [
                'path'   => __DIR__ . '/runtime/cache',
                'expire' => 0
            ]
        ];
        new Cache($config);
    }

    /**
     * @depends testSet
     */
    public function testGet()
    {
        $cache1 = Cache::get('cache1');
        self::assertNull($cache1);

        $cache2 = Cache::get('cache2');
        self::assertNotEmpty($cache2);
    }

    public function testSet()
    {
        Cache::remove('cache2');
        $cache2 = Cache::get('cache2');
        self::assertNull($cache2);
        Cache::set('cache2', 'value2');
        $cache2 = Cache::get('cache2');
        self::assertEquals($cache2, 'value2');
    }

    public function testHas()
    {
        Cache::remove('cache2');
        self::assertFalse(Cache::has('cache2'));

        Cache::set('cache2', 'value2');
        self::assertTrue(Cache::has('cache2'));
    }

    public function testClear()
    {
        self::assertTrue(Cache::has('cache2'));
        Cache::clear();
        self::assertFalse(Cache::has('cache2'));
    }

    public function testRemove()
    {
        Cache::set('cache2', 'value2');
        self::assertTrue(Cache::has('cache2'));
        Cache::remove('cache2');
        self::assertFalse(Cache::has('cache2'));
    }
}
