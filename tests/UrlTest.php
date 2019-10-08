<?php


use fize\framework\Url;
use fize\framework\Config;
use fize\framework\Request;
use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        new Config(__DIR__ . '/config', 'index');

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
        self::assertEquals($org_url1, 'index/news/details?id=110');
        $id = Request::get('id', 0);
        self::assertEquals($id, 110);

        //常规匹配
        $org_url2 = Url::parse('news/13');
        self::assertEquals($org_url2, 'index/news/details');
        $id = Request::get('id', 0);
        self::assertEquals($id, 13);

        //未赋值
        $org_url3 = Url::parse('event/2019/10/08/thisistitle');
        self::assertEquals($org_url3, 'index/event/detail');
        $title = Request::get('title', '');
        self::assertEquals($title, 'thisistitle');

        //非捕获组
        $org_url4 = Url::parse('event2/2019/10/08/thisistitle');
        self::assertEquals($org_url4, 'index/event2/detail');

        //未匹配
        $org_url5 = Url::parse('news2/title110');
        self::assertEquals($org_url5, 'news2/title110');
    }

    public function testCreate()
    {
        //完整匹配
        $url1 = Url::create('index/news/details?id=110');
        var_dump($url1);
        self::assertEquals($url1, 'news/title110');

        //常规匹配
        $url2 = Url::create('index/news/details', ['id' => '111']);
        var_dump($url2);
        self::assertEquals($url2, 'news/111');

        //未赋值
        $url3 = Url::create('index/event/detail', ['year' => '2019', 'month' => '10', 'day' => '08']);
        var_dump($url3);
        self::assertEquals($url3, 'event/2019/10/08/');

        //非捕获组
        $url4 = Url::create('index/event2/detail', ['year' => '2019', 'month' => '10', 'day' => '08']);
        var_dump($url4);
        self::assertEquals($url4, 'event2/2019/10/08');

        //未匹配
        $url5 = Url::create('index/event3/detail', ['year' => '2019', 'month' => '10', 'day' => '08']);
        var_dump($url5);
        self::assertEquals($url5, 'index/event3/detail?year=2019&month=10&day=08');
    }
}
