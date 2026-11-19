<?php

namespace Fize\Framework;

/**
 * 路由
 */
class Route
{

    /**
     * @var array 路由配置
     */
    protected array $config;

    /**
     * 根据配置初始化
     * @param array $config 路由配置
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * 解析查询字符串
     * @param string $query
     * @return array
     */
    private function parseQuery(string $query): array
    {
        $params = [];
        parse_str($query, $params);
        return $params;
    }

    /**
     * 解析伪静态URL并返回实际路由
     * @param string $url
     * @return string
     */
    public function parse(string $url): string
    {
        $request = parse_url($url);
        $requestPath = $request['path'] ?? '';
        $requestParams = isset($request['query']) ? $this->parseQuery($request['query']) : [];

        foreach ($this->config['routes'] ?? [] as $target) {
            $targetUrl = parse_url($target);
            $targetPath = $targetUrl['path'] ?? '';
            $targetParams = isset($targetUrl['query']) ? $this->parseQuery($targetUrl['query']) : [];
            $params = $requestParams;
            $captures = [];

            if (!$this->matchPath($targetPath, $requestPath, $captures)) {
                continue;
            }

            $resolvedPath = $this->replacePathPlaceholders($targetPath, $captures);

            foreach ($targetParams as $key => $value) {
                if (is_string($value) && $this->isPlaceholder($value)) {
                    $name = substr($value, 1, -1);
                    if (array_key_exists($name, $captures)) {
                        $params[$key] = $captures[$name];
                    }
                } else {
                    $params[$key] = $value;
                }
            }

            $_GET = array_merge($_GET, $params);
            return $resolvedPath;
        }

        $_GET = array_merge($_GET, $requestParams);
        return $requestPath;
    }

    /**
     * 根据路由名称生成URL
     * @param string $name 路由名称或实际路由
     * @param array $params 参数
     * @return string
     */
    public function create(string $name, array $params = []): string
    {
        $routes = $this->config['routes'] ?? [];
        if (!array_key_exists($name, $routes)) {
            return $this->appendQuery($name, $params);
        }

        $target = $routes[$name];
        $targetUrl = parse_url($target);
        $path = $targetUrl['path'] ?? '';
        $queryParams = isset($targetUrl['query']) ? $this->parseQuery($targetUrl['query']) : [];
        $queryParams = array_merge($queryParams, $params);
        $path = $this->replacePlaceholders($path, $queryParams);
        $path = $this->replacePlaceholdersInValues($path, $queryParams);

        return $this->appendQuery($path, $queryParams);
    }

    /**
     * 匹配模板路径并提取占位参数
     * @param string $template
     * @param string $path
     * @param array $captures
     * @return bool
     */
    private function matchPath(string $template, string $path, array &$captures): bool
    {
        $templateParts = explode('/', trim($template, '/'));
        $pathParts = explode('/', trim($path, '/'));
        if (count($templateParts) !== count($pathParts)) {
            return false;
        }

        foreach ($templateParts as $index => $part) {
            if ($this->isPlaceholder($part)) {
                $captures[substr($part, 1, -1)] = rawurldecode($pathParts[$index]);
            } elseif ($part !== $pathParts[$index]) {
                return false;
            }
        }
        return true;
    }

    /**
     * 判断字符串是否为占位符
     * @param mixed $value
     * @return bool
     */
    private function isPlaceholder($value): bool
    {
        return is_string($value) && strlen($value) >= 3 && $value[0] === '<' && substr($value, -1) === '>';
    }

    /**
     * 替换路径中的占位符并移除已消费参数
     * @param string $path
     * @param array $params
     * @return string
     */
    private function replacePlaceholders(string $path, array &$params): string
    {
        $parts = explode('/', $path);
        foreach ($parts as &$part) {
            if (!$this->isPlaceholder($part)) {
                continue;
            }
            $name = substr($part, 1, -1);
            if (!array_key_exists($name, $params)) {
                continue;
            }
            $part = rawurlencode((string)$params[$name]);
            unset($params[$name]);
        }
        unset($part);
        return implode('/', $parts);
    }

    /**
     * 使用捕获值替换目标路径中的占位符
     * @param string $path
     * @param array $captures
     * @return string
     */
    private function replacePathPlaceholders(string $path, array $captures): string
    {
        $parts = explode('/', $path);
        foreach ($parts as &$part) {
            if (!$this->isPlaceholder($part)) {
                continue;
            }
            $name = substr($part, 1, -1);
            if (array_key_exists($name, $captures)) {
                $part = $captures[$name];
            }
        }
        unset($part);
        return implode('/', $parts);
    }

    /**
     * 替换查询参数值中的占位符
     * @param string $path
     * @param array $params
     * @return string
     */
    private function replacePlaceholdersInValues(string $path, array &$params): string
    {
        foreach ($params as $key => $value) {
            if (!$this->isPlaceholder($value)) {
                continue;
            }
            $name = substr($value, 1, -1);
            if (!array_key_exists($name, $params)) {
                continue;
            }
            $params[$key] = $params[$name];
            unset($params[$name]);
        }
        return $path;
    }

    /**
     * 将未消费参数附加到URL
     * @param string $url
     * @param array $params
     * @return string
     */
    private function appendQuery(string $url, array $params): string
    {
        if (!$params) {
            return $url;
        }
        $separator = strpos($url, '?') === false ? '?' : '&';
        return $url . $separator . http_build_query($params);
    }
}
