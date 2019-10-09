<?php



use PHPUnit\Framework\TestCase;
use fize\framework\Config;
use fize\framework\Db;

class DbTest extends TestCase
{

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        new Config(__DIR__ . '/config', 'index');

        $db_config = Config::get('db');
        new Db($db_config);
    }

    public function testSelect()
    {
        $rows = Db::table('user')->select();
        var_dump($rows);

        self::assertIsArray($rows);
    }
}
