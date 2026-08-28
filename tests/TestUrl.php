<?php

namespace Tests;

use Fize\Framework\Url;
use PHPUnit\Framework\TestCase;

class TestUrl extends TestCase
{
    /**
     * @var Url
     */
    protected $url;

    protected function setUp(): void
    {
        $_GET = [];
        $this->url = new Url([
            'rules' => [
                'news/title110'                                              => 'index/news/details?id=110',
                'news/(?<id>\d+)'                                            => 'index/news/details',
                'event/(?<year>\d+)/(?<month>\d+)/(?<day>\d+)/(?<title>\S*)' => 'index/event/detail',
                'event2/(?<year>\d+)/(?<month>\d+)/(?<day>\d+)/(?:/?\S*)'    => 'index/event2/detail',
            ],
        ]);
    }

    public function testParse()
    {
        self::assertEquals('index/news/details', $this->url->parse('news/title110'));
        self::assertEquals('110', $_GET['id']);

        self::assertEquals('index/news/details', $this->url->parse('news/13'));
        self::assertEquals('13', $_GET['id']);

        self::assertEquals('index/event/detail', $this->url->parse('event/2019/10/08/thisistitle'));
        self::assertEquals('thisistitle', $_GET['title']);

        self::assertEquals('index/event2/detail', $this->url->parse('event2/2019/10/08/thisistitle'));

        self::assertEquals('news2/title110', $this->url->parse('news2/title110'));
    }

    public function testCreate()
    {
        self::assertEquals('news/title110', $this->url->create('index/news/details?id=110'));
        self::assertEquals('news/111', $this->url->create('index/news/details', ['id' => '111']));
        self::assertEquals('event/2019/10/08/', $this->url->create('index/event/detail', [
            'year'  => '2019',
            'month' => '10',
            'day'   => '08',
        ]));
        self::assertEquals('event2/2019/10/08/', $this->url->create('index/event2/detail', [
            'year'  => '2019',
            'month' => '10',
            'day'   => '08',
        ]));
        self::assertEquals(
            'index/event3/detail?year=2019&month=10&day=08',
            $this->url->create('index/event3/detail', [
                'year'  => '2019',
                'month' => '10',
                'day'   => '08',
            ])
        );
    }
}
