<?php

namespace fize\framework;

/**
 * 请求类
 */
class Request
{

    /**
     * @var string 请求方式
     */
    protected static $method;

    /**
     * 获取原生SERVER
     * @param string $key 键名
     * @param string $default 默认值
     * @return mixed
     */
    public static function server($key = null, $default = null)
    {
        if(is_null($key)) {
            return $_SERVER;
        }
        return isset($_SERVER[$key]) ? $_SERVER[$key] : $default;
    }

    /**
     * 获取原生COOKIE
     * @param string $key 键名
     * @param string $default 默认值
     * @return mixed
     */
    public static function cookie($key = null, $default = null)
    {
        if(is_null($key)) {
            return $_COOKIE;
        }
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
    }

    /**
     * 获取原生SESSION
     * @param  string $key 键名
     * @param  string $default 默认值
     * @return mixed
     */
    public static function session($key = null, $default = null)
    {
        if(is_null($key)) {
            return $_SESSION;
        }
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    /**
     * 获取GET参数
     * @param  string $key 键名
     * @param  string $default 默认值
     * @return mixed
     */
    public static function get($key = null, $default = null)
    {
        if(is_null($key)) {
            return $_GET;
        }
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }

    /**
     * 获取POST参数
     * @param  string $key 键名
     * @param  string $default 默认值
     * @return mixed
     */
    public static function post($key = null, $default = null)
    {
        if(is_null($key)) {
            return $_POST;
        }
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }

    /**
     * 获取上传文件
     * @param string $key 键名
     * @return mixed
     */
    public static function file($key = null)
    {
        if(is_null($key)) {
            return $_FILES;
        }
        return isset($_FILES[$key]) ? $_FILES[$key] : null;
    }

    /**
     * 当前的请求类型
     * @return string
     */
    public static function method()
    {
        if (!self::$method) {
            if (isset($this->post[$this->varMethod])) {
                $method = strtolower($this->post[$this->varMethod]);
                if (in_array($method, ['get', 'post', 'put', 'patch', 'delete'])) {
                    $this->method    = strtoupper($method);
                    $this->{$method} = $this->post;
                } else {
                    $this->method = 'POST';
                }
                unset($this->post[$this->varMethod]);
            } elseif ($this->server('HTTP_X_HTTP_METHOD_OVERRIDE')) {
                $this->method = strtoupper($this->server('HTTP_X_HTTP_METHOD_OVERRIDE'));
            } else {
                $this->method = $this->server('REQUEST_METHOD') ?: 'GET';
            }
        }

        return $this->method;




        if (!$this->method) {
            if (isset($this->post[$this->varMethod])) {
                $method = strtolower($this->post[$this->varMethod]);
                if (in_array($method, ['get', 'post', 'put', 'patch', 'delete'])) {
                    $this->method    = strtoupper($method);
                    $this->{$method} = $this->post;
                } else {
                    $this->method = 'POST';
                }
                unset($this->post[$this->varMethod]);
            } elseif ($this->server('HTTP_X_HTTP_METHOD_OVERRIDE')) {
                $this->method = strtoupper($this->server('HTTP_X_HTTP_METHOD_OVERRIDE'));
            } else {
                $this->method = $this->server('REQUEST_METHOD') ?: 'GET';
            }
        }

        return $this->method;
    }

    /**
     * 是否为GET请求
     * @return bool
     */
    public function isGet()
    {
        return $this->method() == 'GET';
    }

    /**
     * 是否为POST请求
     * @return bool
     */
    public function isPost()
    {
        return $this->method() == 'POST';
    }

    /**
     * 是否为PUT请求
     * @return bool
     */
    public function isPut()
    {
        return $this->method() == 'PUT';
    }

    /**
     * 是否为DELTE请求
     * @return bool
     */
    public function isDelete()
    {
        return $this->method() == 'DELETE';
    }

    /**
     * 是否为HEAD请求
     * @return bool
     */
    public function isHead()
    {
        return $this->method() == 'HEAD';
    }

    /**
     * 是否为PATCH请求
     * @return bool
     */
    public function isPatch()
    {
        return $this->method() == 'PATCH';
    }

    /**
     * 是否为OPTIONS请求
     * @return bool
     */
    public function isOptions()
    {
        return $this->method() == 'OPTIONS';
    }

    /**
     * 是否为cli
     * @return bool
     */
    public function isCli()
    {
        return PHP_SAPI == 'cli';
    }

    /**
     * 是否为cgi
     * @return bool
     */
    public function isCgi()
    {
        return strpos(PHP_SAPI, 'cgi') === 0;
    }

    /**
     * 当前是否ssl
     * @return bool
     */
    public function isSsl()
    {
        if ($this->server('HTTPS') && ('1' == $this->server('HTTPS') || 'on' == strtolower($this->server('HTTPS')))) {
            return true;
        } elseif ('https' == $this->server('REQUEST_SCHEME')) {
            return true;
        } elseif ('443' == $this->server('SERVER_PORT')) {
            return true;
        } elseif ('https' == $this->server('HTTP_X_FORWARDED_PROTO')) {
            return true;
        } elseif ($this->httpsAgentName && $this->server($this->httpsAgentName)) {
            return true;
        }

        return false;
    }

    /**
     * 当前是否JSON请求
     * @return bool
     */
    public function isJson()
    {
        $contentType = $this->contentType();
        $acceptType  = $this->type();

        return false !== strpos($contentType, 'json') || false !== strpos($acceptType, 'json');
    }

    /**
     * 当前是否Ajax请求
     * @param  bool $ajax true 获取原始ajax请求
     * @return bool
     */
    public function isAjax(bool $ajax = false)
    {
        $value  = $this->server('HTTP_X_REQUESTED_WITH');
        $result = $value && 'xmlhttprequest' == strtolower($value) ? true : false;

        if (true === $ajax) {
            return $result;
        }

        return $this->param($this->varAjax) ? true : $result;
    }

    /**
     * 当前是否Pjax请求
     * @param  bool $pjax true 获取原始pjax请求
     * @return bool
     */
    public function isPjax(bool $pjax = false)
    {
        $result = !is_null($this->server('HTTP_X_PJAX')) ? true : false;

        if (true === $pjax) {
            return $result;
        }

        return $this->param($this->varPjax) ? true : $result;
    }

    /**
     * 检测是否使用手机访问
     * @return bool
     */
    public function isMobile()
    {
        if ($this->server('HTTP_VIA') && stristr($this->server('HTTP_VIA'), "wap")) {
            return true;
        } elseif ($this->server('HTTP_ACCEPT') && strpos(strtoupper($this->server('HTTP_ACCEPT')), "VND.WAP.WML")) {
            return true;
        } elseif ($this->server('HTTP_X_WAP_PROFILE') || $this->server('HTTP_PROFILE')) {
            return true;
        } elseif ($this->server('HTTP_USER_AGENT') && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $this->server('HTTP_USER_AGENT'))) {
            return true;
        }

        return false;
    }
}