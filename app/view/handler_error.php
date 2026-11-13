<?php
require_once dirname(__FILE__, 2) . "/common/interiors.php";
/**
 * @var string $errfile 发生错误的文件
 * @var int    $errline 发生的错误行数
 * @var int    $errno   错误码
 * @var string $errstr  错误信息
 */
?><!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>系统发生错误</title>
    <meta name="robots" content="noindex,nofollow"/>
    <style>
        body {
            color: #333;
            font: 16px Consolas, "Liberation Mono", Courier, Verdana, "微软雅黑", serif;
            margin: 0;
            padding: 0 20px 20px;
        }

        h1 {
            margin: 10px 0 0;
            font-size: 28px;
            font-weight: 500;
            line-height: 32px;
        }

        h2 {
            color: #4288ce;
            font-weight: 400;
            padding: 6px 0;
            margin: 6px 0 0;
            font-size: 18px;
            border-bottom: 1px solid #eee;
        }

        h3 {
            margin: 12px;
            font-size: 16px;
            font-weight: bold;
        }

        abbr {
            cursor: help;
            text-decoration: underline;
            text-decoration-style: dotted;
        }

        a {
            color: #868686;
            cursor: pointer;
        }

        a:hover {
            text-decoration: underline;
        }

        .line-error {
            background: #f8cbcb;
        }

        .echo table {
            width: 100%;
        }

        .echo pre {
            padding: 16px;
            overflow: auto;
            font-size: 85%;
            line-height: 1.45;
            background-color: #f7f7f7;
            border: 0;
            border-radius: 3px;
        }

        .echo pre > pre {
            padding: 0;
            margin: 0;
        }

        .exception {
            margin-top: 20px;
        }

        .exception .message {
            padding: 12px;
            border: 1px solid #ddd;
            border-bottom: 0 none;
            line-height: 18px;
            font-size: 16px;
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
        }

        .exception .code {
            float: left;
            text-align: center;
            color: #fff;
            margin-right: 12px;
            padding: 16px;
            border-radius: 4px;
            background: #999;
        }

        .exception .source-code {
            padding: 6px;
            border: 1px solid #ddd;

            background: #f9f9f9;
            overflow-x: auto;

        }

        .exception .source-code pre {
            margin: 0;
        }

        .exception .source-code pre ol {
            margin: 0;
            color: #4288ce;
            display: inline-block;
            min-width: 100%;
            box-sizing: border-box;
            font-size: 14px;
            padding-left: 40px;
        }

        .exception .source-code pre li {
            border-left: 1px solid #ddd;
            height: 18px;
            line-height: 18px;
        }

        .exception .source-code pre code {
            color: #333;
            height: 100%;
            display: inline-block;
            border-left: 1px solid #fff;
            font-size: 14px;
        }

        .exception .trace {
            padding: 6px;
            border: 1px solid #ddd;
            border-top: 0 none;
            line-height: 16px;
            font-size: 14px;
        }

        .exception .trace ol {
            margin: 12px;
        }

        .exception .trace ol li {
            padding: 2px 4px;
        }

        .exception div:last-child {
            border-bottom-left-radius: 4px;
            border-bottom-right-radius: 4px;
        }
    </style>
</head>
<body>
<div class="exception">
    <div class="message">

        <div class="info">
            <div>
                <h2>
                    [<?php echo $errno; ?>]&nbsp;
                    <?php echo sprintf('%s in %s', parse_class($errfile), parse_file($errfile, $errline)); ?></h2>
            </div>
            <div><h1><?php echo nl2br(htmlentities($errstr)); ?></h1></div>
        </div>

    </div>


    <div class="trace">
        <h2>Call Stack</h2>
        <ol>
            <?php
            $traces = debug_backtrace();
            unset($traces[0]);
            unset($traces[1]);
            foreach ($traces as $trace) {
                ?>
                <li>
                    <?php
                    if ($trace['function']) {
                        echo sprintf(
                            'at %s%s%s(%s)',
                            isset($trace['class']) ? parse_class($trace['class']) : '',
                            $trace['type'] ?? '',
                            $trace['function'],
                            isset($trace['args']) ? parse_args($trace['args']) : ''
                        );
                    }

                    if (isset($trace['file']) && isset($trace['line'])) {
                        echo sprintf(' in %s', parse_file($trace['file'], $trace['line']));
                    }
                    ?>
                </li>
            <?php } ?>
        </ol>
    </div>
</div>
</body>
</html>
