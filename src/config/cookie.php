<?php

/**
 * COOKIE设置
 */
return [
    'expire'        => 3600, //cookie有效时间，以秒为单位
    'path'          => "/", //Cookie路径
    'domain'        => "", //Cookie有效域名
    'secure'        => false, //是否只允许在HTTPS安全链接下生效
    'httponly'      => true, //是否使用httponly，为安全性，全局默认开启
    'prefix'        => "", //Cookie键名前缀,如果发生冲突可以修改该值
    'encrypt_key'   => false, //是否加密cookie键名，加密键名则需要对所有cookie进行遍历获取，不合适cookie过多的情况
    'encrypt_value' => true, //是否加密cookie键值
    'secret_key'    => "", //加密密钥
];
