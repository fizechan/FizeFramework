<?php


namespace fize\framework;

use fize\db\Db as FizeDb;

/**
 * 数据库
 */
class Db extends FizeDb
{

    /**
     * 构造为静态方法做准备
     * @param array $config 配置
     */
    public function __construct(array $config)
    {
        if(isset($config['type']) && $config['type']) {
            self::init($config);
        }
    }
}