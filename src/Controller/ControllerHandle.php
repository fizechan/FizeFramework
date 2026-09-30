<?php

namespace Fize\Framework\Controller;

use think\App;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\facade\Config;
use think\facade\Request;
use think\facade\View;
use think\Response;
use think\Validate;

/**
 * 控制器处理类
 */
class ControllerHandle
{

    /**
     * 应用实例
     * @var App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 构造方法
     * @param App  $app           应用对象
     * @param bool $batchValidate 是否批量验证
     */
    public function __construct(App $app, bool $batchValidate = false)
    {
        $this->app = $app;
        $this->batchValidate = $batchValidate;
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
    public function validate(array $data = null, $validate = null, array $message = [], bool $batch = false, bool $throw = true): bool
    {
        if (is_null($data)) {
            $data = Request::post();
        }
        /**
         * @var Validate $v
         */
        if (is_null($validate)) {
            $validate = '\\app\\' . app('http')->getName() . '\\validate\\' . Request::controller();
            $class = $this->app->parseClass('validate', $validate);
            if (!class_exists($class)) {
                return true;
            }
            $v = new $class();
            if (!$v->hasScene(Request::action())) {  // 以action作为场景
                return true;
            }
            $v->scene(Request::action());
        } elseif (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch();
        }

        return $v->failException($throw)->check($data);
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
    public function success($msg = '', $data = '', string $url = null, int $code = 0, int $wait = 3, array $header = [])
    {
        if (is_null($url) && !is_null(Request::server('HTTP_REFERER'))) {
            $url = Request::server('HTTP_REFERER');
        } elseif ('' !== $url && !strpos($url, '://') && 0 !== strpos($url, '/')) {
            $url = url($url);
        }
        $type = $this->getResponseType();
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];
        if ('html' == strtolower($type)) {
            $result = View::fetch(Config::get('app.dispatch_success_tmpl'), $result);
        }
        $response = Response::create($result, $type)->header($header);
        throw new HttpResponseException($response);
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
    public function error($msg = '', int $code = 500, string $url = null, $data = '', int $wait = 3, array $header = [])
    {
        $type = $this->getResponseType();
        if (is_null($url)) {
            $url = $this->app['request']->isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ('' !== $url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : $this->app['url']->build($url);
        }
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];
        if ('html' == strtolower($type)) {
            $type = 'jump';
        }
        $response = Response::create($result, $type)->header($header)->options(['jump_template' => $this->app['config']->get('dispatch_error_tmpl')]);
        throw new HttpResponseException($response);
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
    public function result($data, $msg = '', int $code = 0, string $type = '', array $header = [])
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
            'time' => Request::server('REQUEST_TIME')
        ];
        $type = $type ?: $this->getResponseType();
        $response = Response::create($result, $type)->header($header);
        throw new HttpResponseException($response);
    }

    /**
     * URL 重定向.
     *
     * @param string    $url    跳转的 URL 表达式
     * @param array|int $params 其它 URL 参数
     * @param int       $code   http code
     * @param array     $with   隐式传参
     */
    public function redirect(string $url, $params = [], int $code = 302, array $with = [])
    {
        if (is_int($params)) {
            $code = $params;
        }
        $response = redirect($url);
        $response->code($code)->with($with);
        throw new HttpResponseException($response);
    }

    /**
     * 获取当前的 response 输出类型.
     *
     * @return string
     */
    protected function getResponseType(): string
    {
        return Request::isAjax()
            ? Config::get('app.default_ajax_return')
            : Config::get('app.default_return_type');
    }

}