<?php
/**
 * +----------------------------------------------------------------------
 * | UPADD [ Can be better to Up add]
 * +----------------------------------------------------------------------
 * | Copyright (c) 2011-2016 http://upadd.cn All rights reserved.
 * +----------------------------------------------------------------------
 * | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 * +----------------------------------------------------------------------
 * | Author: Richard.z <v3u3i87@gmail.com>
 **/
if (version_compare(PHP_VERSION, '5.5.0', '<')) exit ('require PHP > 5.5.0 !');


define('VENDOR', 'vendor/Upadd');
define('UPADD_HOST', substr(dirname(__FILE__), 0, -12));

// 函数库
require UPADD_HOST . VENDOR . '/Public/help.php';

use Upadd\Bin\Grab;
use Upadd\Bin\Factory;
use Upadd\Bin\Loader;

/**
 * 实例化APP
 */
$app = new \Upadd\Bin\Application();

/**
 * 设置配置文件
 */
$app->getConfig();

/**
 * 加载组件
 */
Loader::Run();

/**
 * 设置Session
 */
$app->setSession();

/**
 * 实例化模块
 */
$app->getWorkModule();

/**
 * 导入实例化模块
 */
Factory::Import($app->_work);

/**
 * 载入别名
 */
$app->getAlias()->run();


Grab::run();
/**
 * 运行
 */
$app->run(function () use ($app) {

    $_hostConfigPath = host() . 'config';

    /**
     * 扩展文件
     */
    $extend = $_hostConfigPath . '/extend.php';
    file_exists($extend) && require $extend;

    /**
     * 路由配置
     */
    $routing = $_hostConfigPath . '/routing.php';
    file_exists($routing) && require $routing;

    /**
     * 过滤器
     */
    $filters = $_hostConfigPath . '/filters.php';
    file_exists($filters) && require $filters;

}, isset($argv) ? $argv : array());

