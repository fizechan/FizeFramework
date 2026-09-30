<?php

namespace Fize\Framework\Controller;

use think\App;
use think\exception\HttpResponseException;
use think\exception\ValidateException;

/**
 * 公用控制器
 */
abstract class CommonController extends SimpleController
{

    /**
     * 构造方法
     * @param App $app 应用对象
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {
    }

    /**
     * 验证数据
     * @param array|null        $data     数据，null表示当前POST数据
     * @param string|array|null $validate 验证器名或者验证规则数组，null表示使用控制器同名验证器
     * @param array             $message  提示信息
     * @param bool              $batch    是否批量验证
     * @param bool              $throw    验证不通过时是否抛出错误
     * @return bool
     * @throws ValidateException
     */
    protected function validate(array $data = null, $validate = null, array $message = [], bool $batch = false, bool $throw = true): bool
    {
        return $this->handle->validate($data, $validate, $message, $batch, $throw);
    }

    /**
     * 操作成功跳转的快捷方法.
     *
     * @param mixed       $msg    提示信息
     * @param mixed       $data   返回的数据
     * @param string|null $url    跳转的 URL 地址
     * @param int         $code   返回的 code
     * @param int         $wait   跳转等待时间
     * @param array       $header 发送的 Header 信息
     */
    protected function success($msg = '', $data = '', string $url = null, int $code = 0, int $wait = 3, array $header = [])
    {
        $this->handle->success($msg, $data, $url, $code, $wait, $header);
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param mixed       $msg    提示信息
     * @param int         $code   返回的 code
     * @param string|null $url    跳转的URL地址
     * @param mixed       $data   返回的数据
     * @param int         $wait   跳转等待时间
     * @param array       $header 发送的Header信息
     * @throws HttpResponseException
     */
    protected function error($msg = '', int $code = 500, string $url = null, $data = '', int $wait = 3, array $header = [])
    {
        $this->handle->error($msg, $code, $url, $data, $wait, $header);
    }

    /**
     * 返回封装后的 API 数据到客户端.
     *
     * @param mixed  $data   要返回的数据
     * @param mixed  $msg    提示信息
     * @param int    $code   返回的 code
     * @param string $type   返回数据格式
     * @param array  $header 发送的 Header 信息
     */
    protected function result($data, $msg = '', int $code = 0, string $type = '', array $header = [])
    {
        $this->handle->result($data, $msg, $code, $type, $header);
    }

    /**
     * URL 重定向.
     *
     * @param string    $url    跳转的 URL 表达式
     * @param array|int $params 其它 URL 参数
     * @param int       $code   http code
     * @param array     $with   隐式传参
     */
    protected function redirect(string $url, $params = [], int $code = 302, array $with = [])
    {
        $this->handle->redirect($url, $params, $code, $with);
    }
}