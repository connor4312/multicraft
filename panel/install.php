<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

$yii = dirname(__FILE__).'/protected/yii/yii.php';
$config = dirname(__FILE__).'/protected/config/internal/install.php';

defined('YII_DEBUG') or define('YII_DEBUG',true);
// how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);

require_once($yii);
$app = Yii::createWebApplication($config);
$app->params['installer'] = 'show';
if (preg_match('/^(site\/log(in|out)|install\/)/', @$_GET['r']))
    $app->run();
else
    $app->runController('/install');
