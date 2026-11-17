<?php

namespace Fize\Framework\Facade;

use Fize\Database\Core\Db as CoreDb;
use Fize\Database\Db as FizeDb;

class Db
{

    protected static $db;

    protected static function checkInit()
    {
        if (self::$db !== null) {
            return;
        }
        $config = Config::get('database');
        self::$db = new FizeDb($config['type'], $config['config'], $config['mode'] ?? null);
    }

    /**
     * 取得一个新的连接
     * @param string      $type   数据库类型
     * @param array       $config 数据库配置项
     * @param string|null $mode   连接模式
     * @return CoreDb
     */
    public static function connect(string $type, array $config, ?string $mode = null): CoreDb
    {
        self::checkInit();
        return FizeDb::connect($type, $config, $mode);
    }

    /**
     * 执行一个SQL查询
     * @param string        $sql      SQL语句，支持问号预处理
     * @param array         $params   可选的绑定参数
     * @param callable|null $callback 如果定义该记录集回调函数则不返回数组而直接进行循环回调
     * @return array 返回结果数组
     */
    public static function query(string $sql, array $params = [], ?callable $callback = null): array
    {
        self::checkInit();
        return FizeDb::query($sql, $params, $callback);
    }

    /**
     * 执行一个SQL语句
     * @param string $sql    SQL语句，支持问号预处理语句
     * @param array  $params 可选的绑定参数
     * @return int 返回受影响行数
     */
    public function execute(string $sql, array $params = []): int
    {
        self::checkInit();
        return FizeDb::execute($sql, $params);
    }

    /**
     * 开始事务
     */
    public static function startTrans()
    {
        self::checkInit();
        FizeDb::startTrans();
    }

    /**
     * 执行事务
     */
    public static function commit()
    {
        self::checkInit();
        FizeDb::commit();
    }

    /**
     * 回滚事务
     */
    public static function rollback()
    {
        self::checkInit();
        FizeDb::rollback();
    }

    /**
     * 指定当前要操作的表
     *
     * 支持链式调用
     * @param string      $name   表名
     * @param string|null $prefix 表前缀，默认为null表示使用当前前缀
     * @return CoreDb
     */
    public static function table(string $name, ?string $prefix = null): CoreDb
    {
        self::checkInit();
        return FizeDb::table($name, $prefix);
    }

    /**
     * 获取最后运行的SQL
     *
     * 仅供日志使用的SQL语句，由于本身存在SQL危险请不要真正用于执行
     * @param bool $real 是否返回最终SQL语句而非预处理语句
     * @return string
     */
    public static function getLastSql(bool $real = false): string
    {
        self::checkInit();
        return FizeDb::getLastSql($real);
    }

}