<?php

namespace fize\framework;

use fize\framework\exception\ResponseException;
use fize\security\Validator;
use fize\view\View;
use fize\view\ViewFactory;
use fize\web\Request;
use fize\web\Response;

/**
 * 控制器
 */
abstract class Controller
{

    /**
     * 返回JSON结果
     * @param array       $data    数据
     * @param string|null $message 错误信息
     * @param int         $code    错误码
     */
    protected function result(array $data, string $message = null, int $code = 0)
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
     * @param string      $message 错误信息
     * @param string|null $url     回跳URL
     * @param int         $code    错误码
     */
    protected function success(string $message, string $url = null, int $code = 0)
    {
        if (Request::isAjax()) {
            $json = [
                'code'    => $code,
                'message' => $message
            ];
            $response = Response::json($json);
        } else {
            $config_view = Config::get('view');
            if ($config_view['tpl_success']) {
                View::path($config_view['tpl_success']);
                View::assign('message', $message);
                View::assign('url', $url);
                View::assign('code', $code);
                $response = Response::html(View::render());
            } else {
                $view = ViewFactory::create('Php', ['view' => __DIR__ . '/view']);
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
     * @param string $message 错误信息
     * @param int    $code    错误码
     */
    protected function error(string $message, int $code = 0)
    {
        if (Request::isAjax()) {
            $json = [
                'code'    => $code,
                'message' => $message
            ];
            $response = Response::json($json);
        } else {
            $config_view = Config::get('view');
            if ($config_view['tpl_error']) {
                View::path($config_view['tpl_error']);
                View::assign('message', $message);
                View::assign('code', $code);
                $response = Response::html(View::render());
            } else {
                $view = ViewFactory::create('Php', ['view' => __DIR__ . '/view']);
                $view->assign('message', $message);
                $view->assign('code', $code);
                $response = Response::html($view->render('error'));
            }
        }
        throw new ResponseException($response);
    }

    /**
     * 跳转
     * @param string   $url    内部URL
     * @param array    $params 附加的URL参数
     * @param int|null $delay  延迟时间，以秒为单位
     */
    protected function redirect(string $url, array $params = [], int $delay = null)
    {
        $url = Url::create($url, $params);
        $response = Response::redirect($url, $delay);
        throw new ResponseException($response);
    }

    /**
     * 验证数据
     * @param array|null $data 数据
     */
    protected function validate(array $data = null)
    {
        $config_validator = Config::get('validator');

        $path = '\\' . Env::appDir() . '\\' . App::module() . '\\' . $config_validator['dir'] . '\\' . App::controller();
        $class = str_replace('\\', DIRECTORY_SEPARATOR, $path . $config_validator['postfix']);
        if (!class_exists($class)) {
            $class = str_replace('\\', DIRECTORY_SEPARATOR, $path);
        }
        if (!class_exists($class)) {
            $path = '\\' . Env::appDir() . '\\common\\' . $config_validator['dir'] . '\\' . App::controller();
            $class = str_replace('\\', DIRECTORY_SEPARATOR, $path . $config_validator['postfix']);
            if (!class_exists($class)) {
                $class = str_replace('\\', DIRECTORY_SEPARATOR, $path);
            }
        }

        if (class_exists($class)) {
            /**
             * @var Validator $validator
             */
            $validator = new $class();
            if ($validator->hasScene(App::action())) {
                $validator->scene(App::action());
            }
            $result = $validator->check($data);
            if ($result !== true) {
                $this->error($result);
            }
        }
    }
}
