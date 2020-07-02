<?php

namespace fize\framework;

use fize\misc\Preg;

/**
 * URL管理
 */
class Url
{

    /**
     * @var array 当前配置
     */
    protected static $config;

    /**
     * 根据配置初始化
     * @param array $config URL配置
     */
    public function __construct(array $config)
    {
        self::$config = $config;
    }

    /**
     * 解析url中参数信息，返回参数数组
     * @param string $query 请求GET字符串
     * @return array
     */
    private static function convertQuery($query)
    {
        $parts = explode('&', $query);
        $params = [];
        foreach ($parts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }
        return $params;
    }

    /**
     * 解析URL获得实际URL路由
     * @param string $url 待解析URL
     * @return string
     */
    public static function parse($url)
    {
        $rules = self::$config['rules'];
        foreach ($rules as $pattern => $target) {
            if (Preg::match("#^{$pattern}$#", $url, $matches)) {  // 命中路由规则
                //改装$target成url
                if ($matches) {  // 捕获匹配组
                    foreach ($matches as $key => $value) {
                        if ($key === 0) {  // 第一个匹配组为其本身
                            continue;
                        }
                        if (is_int($key)) {  // 键名为数字时忽略
                            continue;
                        }
                        if (strstr($target, "<{$key}>") === false) {  // 捕获组在URL中无定义则作为GET参数传递
                            $_GET[$key] = $value;
                        } else {  // 捕获组在URL中有定义则进行URL替换
                            $target = str_replace("<{$key}>", $value, $target);
                        }
                    }
                }

                //解析GET参数，并注入到 $_GET 中去
                $turl = parse_url($target);
                if (isset($turl['query'])) {
                    $gets = self::convertQuery($turl['query']);
                    foreach ($gets as $key => $value) {
                        $_GET[$key] = $value;
                    }
                }
                return $turl['path'];
            }
        }
        $turl = parse_url($url);
        return $turl['path'];
    }

    /**
     * 给URL附加参数并返回新的URL
     * @param string $url    URL
     * @param array  $params 要附加的参数
     * @return string 返回新的URL
     */
    private static function appendQuery($url, array $params)
    {
        $query = '';
        foreach ($params as $key => $value) {
            if (!$query) {
                $query = urlencode($key) . "=" . urlencode($value);
            } else {
                $query .= "&" . urlencode($key) . "=" . urlencode($value);
            }
        }
        if ($query) {
            if (strstr($url, '?') === false) {
                $url .= "?" . $query;
            } else {
                $url .= "&" . $query;
            }
        }
        return $url;
    }

    /**
     * 构造URL
     * @param string $url    原URL
     * @param array  $params 附加参数
     * @return string
     */
    public static function create($url, array $params = [])
    {
        $full_url = self::appendQuery($url, $params);

        $finial_url = $full_url;

        $turl = parse_url($full_url);
        $full_params = [];
        if (isset($turl['query'])) {
            $full_params = self::convertQuery($turl['query']);
        }

        $test_urls = [$full_url, $url];
        foreach ($test_urls as $test_url) {
            if ($pattern = array_search($test_url, self::$config['rules'])) {  //命中路由规则
                //改装$pattern成url
                $finial_url = $pattern;

                //清理非捕获组
                $finial_url = Preg::replace('#\(\?\:[^\)]*\)#', '', $finial_url);

                //匹配捕获组并替换
                while (true) {
                    if (Preg::match('#\(\?\<(?<name>[^\>]*)\>[^\)]*\)#', $finial_url, $matches)) {
                        if (isset($matches['name'])) {
                            if (isset($full_params[$matches['name']])) {
                                $finial_url = str_replace($matches[0], $full_params[$matches['name']], $finial_url);
                            } else {
                                $finial_url = str_replace($matches[0], '', $finial_url);  // 未传入该参数则为空
                            }
                        }
                    } else {
                        break;
                    }
                }

            }
        }

        return $finial_url;
    }
}
