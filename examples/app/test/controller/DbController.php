<?php


namespace app\test\controller;

use Fize\Database\Db;
use Fize\Database\Query;


class DbController
{

    public function select()
    {
        $rows = Db::table('user')->select();
        var_dump($rows);
    }

    public function query()
    {
        $query = Query::field('id')->eq(69);
        $rows = Db::table('user')->where($query)->select();
        var_dump($rows);
    }
}