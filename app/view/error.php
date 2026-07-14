<?php
/**
 * @var string $message 错误描述
 * @var int    $code    错误码
 */
?><!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title><?php echo $message; ?></title>
</head>
<body>
<h1>error!</h1>
<h2><?php echo $message; ?></h2>
<h2><?php echo $code; ?></h2>
</body>
</html>
