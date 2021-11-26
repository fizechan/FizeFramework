<?php

namespace Tests;

use Fize\Framework\Config;
use Fize\Framework\Url;
use Fize\Web\Request;
use PHPUnit\Framework\TestCase;

class TestUrl extends TestCase
{

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        new Config(dirname(__DIR__) . '/temp/config', 'index');

        $url_config = Config::get('url');
        new Url($url_config);
    }

    public function test__construct()
    {
        self::assertEquals(0, 0);
    }

    public function testParse()
    {
        //完整匹配
        $org_url1 = Url::parse('news/title110');
        self::assertEquals('index/news/details', $org_url1);
        $id = Request::get('id', 0);
        self::assertEquals(110, $id);

        //常规匹配
        $org_url2 = Url::parse('news/13');
        self::assertEquals('index/news/details', $org_url2);
        $id = Request::get('id', 0);
        self::assertEquals(13, $id);

        //未赋值
        $org_url3 = Url::parse('event/2019/10/08/thisistitle');
        self::assertEquals('index/event/detail', $org_url3);
        $title = Request::get('title', '');
        self::assertEquals('thisistitle', $title);

        //非捕获组
        $org_url4 = Url::parse('event2/2019/10/08/thisistitle');
        self::assertEquals('index/event2/detail', $org_url4);

        //未匹配
        $org_url5 = Url::parse('news2/title110');
        self::assertEquals('news2/title110', $org_url5);
    }

    public function testCreate()
    {
        //完整匹配
        $url1 = Url::create('index/news/details?id=110');
        var_dump($url1);
        self::assertEquals('news/title110', $url1);

        //常规匹配
        $url2 = Url::create('index/news/details', ['id' => '111']);
        var_dump($url2);
        self::assertEquals('news/111', $url2);

        //未赋值
        $url3 = Url::create('index/event/detail', ['year' => '2019', 'month' => '10', 'day' => '08']);
        var_dump($url3);
        self::assertEquals('event/2019/10/08/', $url3);

        //非捕获组
        $url4 = Url::create('index/event2/detail', ['year' => '2019', 'month' => '10', 'day' => '08']);
        var_dump($url4);
        self::assertEquals('event2/2019/10/08', $url4);

        //未匹配
        $url5 = Url::create('index/event3/detail', ['year' => '2019', 'month' => '10', 'day' => '08']);
        var_dump($url5);
        self::assertEquals('index/event3/detail?year=2019&month=10&day=08', $url5);
    }
}
