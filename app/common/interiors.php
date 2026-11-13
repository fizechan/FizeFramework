<?php

/**
 * @param $source
 * @return int
 */
function parse_padding($source)
{
    $length = strlen(strval(count($source['source']) + $source['first']));
    return 40 + ($length - 1) * 8;
}

/**
 * 解析类
 * @param string $name 类名
 * @return string
 */
function parse_class(string $name): string
{
    $names = explode('\\', $name);
    return '<abbr title="' . $name . '">' . end($names) . '</abbr>';
}

/**
 * 解析文件
 * @param string $file 文件
 * @param int    $line 行号
 * @return string
 */
function parse_file(string $file, int $line): string
{
    return '<a class="toggle" title="' . "{$file} line {$line}" . '">' . basename($file) . " line {$line}" . '</a>';
}

/**
 * 解析参数
 * @param array $args 参数
 * @return string
 */
function parse_args(array $args): string
{
    $result = [];

    foreach ($args as $key => $item) {
        switch (true) {
            case is_object($item):
                $value = sprintf('<em>object</em>(%s)', parse_class(get_class($item)));
                break;
            case is_array($item):
                if (count($item) > 3) {
                    $value = sprintf('[%s, ...]', parse_args(array_slice($item, 0, 3)));
                } else {
                    $value = sprintf('[%s]', parse_args($item));
                }
                break;
            case is_string($item):
                if (strlen($item) > 20) {
                    $value = sprintf(
                        '\'<a class="toggle" title="%s">%s...</a>\'',
                        htmlentities($item),
                        htmlentities(substr($item, 0, 20))
                    );
                } else {
                    $value = sprintf("'%s'", htmlentities($item));
                }
                break;
            case is_int($item):
            case is_float($item):
                $value = $item;
                break;
            case is_null($item):
                $value = '<em>null</em>';
                break;
            case is_bool($item):
                $value = '<em>' . ($item ? 'true' : 'false') . '</em>';
                break;
            case is_resource($item):
                $value = '<em>resource</em>';
                break;
            default:
                $value = htmlentities(str_replace("\n", '', var_export(strval($item), true)));
                break;
        }

        $result[] = is_int($key) ? $value : "'{$key}' => {$value}";
    }

    return implode(', ', $result);
}