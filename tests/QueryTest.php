<?php


use fize\framework\Query;
use fize\framework\Config;
use fize\framework\Db;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        new Config(__DIR__ . '/config', 'index');

        $db_config = Config::get('db');
        new Db($db_config);
    }

    public function testQuery()
    {
        $query = Query::field('id')->eq(69);
        $rows = Db::table('user')->where($query)->select();
        var_dump($rows);

        self::assertIsArray($rows);
        self::assertEquals(count($rows), 1);
    }
}
