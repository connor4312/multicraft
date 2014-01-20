<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

$yii = dirname(__FILE__).'/protected/yii/yii.php';
$config = dirname(__FILE__).'/protected/config/internal/api.php';

// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);

require_once($yii);
$app = Yii::createWebApplication($config);
$method = @$_REQUEST[ApiController::$methodField];
if(!preg_match('/^\w+$/',$method))
    $method = 'error';
$app->runController('api/'.$method);
