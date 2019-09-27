<?php


namespace fize\framework;

use fize\log\Log as FizeLog;
use fize\log\LogHandler;

/**
 * 日志类
 * 遵循PSR3规范，并将其以静态方法实现便于调用
 * @package fize\framework
 */
class Log
{

    const LEVEL_EMERGENCY = 'emergency';

    const LEVEL_ALERT     = 'alert';

    const LEVEL_CRITICAL  = 'critical';

    const LEVEL_ERROR     = 'error';

    const LEVEL_WARNING   = 'warning';

    const LEVEL_NOTICE    = 'notice';

    const LEVEL_INFO      = 'info';

    const LEVEL_DEBUG     = 'debug';

    /**
     * @var LogHandler
     */
    protected static $log;

    /**
     * 在构造方法中设置静态属性
     * @param array $config 配置项
     */
    public function __construct(array $config = [])
    {
        self::$log = FizeLog::getInstance($config['handler'], $config['config']);
    }

    /**
     * 系统无法使用。
     * @param string $message 日志内容
     * @param array $context 上下文配置
     */
    public static function emergency($message, array $context = [])
    {
        self::log(self::LEVEL_EMERGENCY, $message, $context);
    }

    /**
     * 必须立即采取行动。例如: 整个网站宕机了，数据库挂了，等等。 这应该发送短信通知警告你.
     * @param string $message 日志内容
     * @param array $context 上下文配置
     */
    public static function alert($message, array $context = [])
    {
        self::log(self::LEVEL_ALERT, $message, $context);
    }

    /**
     * 临界条件。例如: 应用组件不可用，意外的异常。
     * @param string $message 日志内容
     * @param array $context 上下文配置
     */
    public static function critical($message, array $context = [])
    {
        self::log(self::LEVEL_CRITICAL, $message, $context);
    }

    /**
     * 运行时错误不需要马上处理，但通常应该被记录和监控。
     * @param string $message 日志内容
     * @param array $context 上下文配置
     */
    public static function error($message, array $context = [])
    {
        self::log(self::LEVEL_ERROR, $message, $context);
    }

    /**
     * 例外事件不是错误。例如: 使用过时的API，API使用不当，不合理的东西不一定是错误。
     * @param string $message 日志内容
     * @param array $context 上下文配置
     */
    public static function warning($message, array $context = [])
    {
        self::log(self::LEVEL_WARNING, $message, $context);
    }

    /**
     * 正常但重要的事件.
     * @param string $message 日志内容
     * @param array $context 上下文配置
     */
    public static function notice($message, array $context = [])
    {
        self::log(self::LEVEL_NOTICE, $message, $context);
    }

    /**
     * 有趣的事件.
     * 例如: 用户登录，SQL日志。
     * @param string $message 日志内容
     * @param array $context 上下文配置
     */
    public static function info($message, array $context = [])
    {
        self::log(self::LEVEL_INFO, $message, $context);
    }

    /**
     * 详细的调试信息。
     * @param string $message 日志内容
     * @param array $context 上下文配置
     */
    public static function debug($message, array $context = [])
    {
        self::log(self::LEVEL_DEBUG, $message, $context);
    }

    /**
     * 可任意级别记录日志。
     * @param string $level 日志级别
     * @param string $message 日志内容
     * @param array $context 上下文配置
     */
    public static function log($level, $message, array $context = [])
    {
        self::$log->write($message, $level, $context);
    }

}