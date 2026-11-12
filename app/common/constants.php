<?php

use Fize\Framework\App;

/**
 * 根目录路径
 */
define('FIZE_ROOT_PATH', App::getInstance()->boot->rootPath());

/**
 * 应用目录路径
 */
define('FIZE_APP_PATH', App::getInstance()->boot->appPath());

/**
 * 配置目录路径
 */
define('FIZE_CONFIG_PATH', App::getInstance()->boot->configPath());

/**
 * 运行目录路径
 */
define('FIZE_RUNTIME_PATH', App::getInstance()->boot->runtimePath());

/**
 * 视图目录路径
 */
define('FIZE_VIEW_PATH', App::getInstance()->boot->viewPath());