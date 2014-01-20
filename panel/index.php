<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

$yii = dirname(__FILE__).'/protected/yii/yii.php';
$config = dirname(__FILE__).'/protected/config/internal/application.php';

// uncomment the following line to enable more detailed error messages
//defined('YII_DEBUG') or define('YII_DEBUG',true);
// how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);

if (!@file_exists(dirname(__FILE__).'/protected/config/config.php'))
    header('Location: install.php');
else
{
    require_once($yii);
    Yii::createWebApplication($config)->run();
}
