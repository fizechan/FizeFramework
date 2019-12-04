<?php


namespace fize\framework;

use fize\framework\exception\ResponseException;
use fize\web\Response;
use fize\web\Request;
use fize\view\View;

/**
 * 控制器
 */
class Controller
{

    /**
     * 返回JSON结果
     * @param array $data data字段
     * @param string $message message字段
     * @param int $code code字段
     */
    protected function result(array $data, $message = null, $code = 0)
    {
        $json = [
            'code'    => $code,
            'message' => $message,
            'data'    => $data
        ];
        throw new ResponseException(Response::json($json));
    }

    /**
     * 成功操作
     * @param string $message 提示信息
     * @param string $url 回跳URL
     * @param int $code code字段
     */
    protected function success($message, $url = null, $code = 0)
    {
        if (Request::isAjax()) {
            $json = [
                'code'    => $code,
                'message' => $message
            ];
            $response = Response::json($json);
        } else {
            $config_view = Config::get('view');
            if($config_view['dispatch_success_tmpl']) {
                View::path($config_view['dispatch_success_tmpl']);
                View::assign('message', $message);
                View::assign('url', $url);
                View::assign('code', $code);
                $response = Response::html(View::render());
            } else {
                $view = View::getInstance('Php', ['view' => __DIR__ . '/view']);
                $view->assign('message', $message);
                $view->assign('url', $url);
                $view->assign('code', $code);
                $response = Response::html($view->render('success'));
            }
        }
        throw new ResponseException($response);
    }

    /**
     * 失败操作
     * @param string $message message字段
     * @param int $code code字段
     */
    protected function error($message, $code = 0)
    {
        if (Request::isAjax()) {
            $json = [
                'code'    => $code,
                'message' => $message
            ];
            $response = Response::json($json);
        } else {
            $config_view = Config::get('view');
            if($config_view['dispatch_success_tmpl']) {
                View::path($config_view['dispatch_error_tmpl']);
                View::assign('message', $message);
                View::assign('code', $code);
                $response = Response::html(View::render());
            } else {
                $view = View::getInstance('Php', ['view' => __DIR__ . '/view']);
                $view->assign('message', $message);
                $view->assign('code', $code);
                $response = Response::html($view->render('error'));
            }
        }
        throw new ResponseException($response);
    }

    /**
     * 跳转
     * @param string $url 内部URL
     * @param array $params 附加的URL参数
     * @param int $delay 延迟时间，以秒为单位
     */
    protected function redirect($url, array $params = [], $delay = null)
    {
        $url = Url::create($url, $params);
        $response = Response::redirect($url, $delay);
        throw new ResponseException($response);
    }
}