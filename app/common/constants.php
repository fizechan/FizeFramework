<?php

use Fize\Framework\App;

/**
 * 应用根目录路径
 */
define('FIZE_ROOT_PATH', App::getInstance()->boot->rootPath());

/**
 * 应用根目录路径
 */
define('FIZE_RUNTIME_PATH', App::getInstance()->boot->runtimePath());

/**
 * 应用视图目录路径
 */
define('FIZE_VIEW_PATH', App::getInstance()->boot->appPath() . '/' . App::getInstance()->boot->viewDir());