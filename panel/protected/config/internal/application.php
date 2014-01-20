<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
/**
 * For basic configuration options please see "config.php"
 **/

if (@file_exists(dirname(__FILE__).'/../config.php'))
    $config = require(dirname(__FILE__).'/../config.php');
else
    $config = array();

if (!@$config['daemon_password'])
    $config['daemon_password'] = 'none';
if (!@$config['superuser'])
    $config['superuser'] = 'admin';
if (!@$config['timeout'])
    $config['timeout'] = 5;

$params = $config;
$params['installer'] = false;
//$params['demo_mode'] = 'enabled';
unset($params['daemon_db']);
unset($params['daemon_db_user']);
unset($params['daemon_db_pass']);
unset($params['panel_db']);
unset($params['panel_db_user']);
unset($params['panel_db_pass']);


return array(
    'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..',
    'name'=>'Multicraft',

    // preloading 'log' component
    'preload'=>array('log'),

    'import'=>array(
        'application.extensions.*',
        'application.models.*',
        'application.components.*',
        'ext.yii-mail.YiiMailMessage',
    ),

    'modules'=>array(
    ),

    // application components
    'components'=>array(
        'user'=>array(
            'class'=>'WebUser',
            // enable cookie-based authentication
            'allowAutoLogin'=>true,
        ),
        'authManager'=>array(
            'class'=>'DbAuthManager',
            'connectionID'=>'db',
            'defaultRoles'=>array('user', 'guest'),
        ),
        'request'=>array(
            'enableCookieValidation'=>@$config['enable_cookie_validation'],
            'enableCsrfValidation'=>@$config['enable_csrf_validation'],
        ),
        'urlManager'=>array(
            // uncomment the following to enable URLs in the format index.php/server/1
            /*'urlFormat'=>'path',*/
            // uncomment the following to hide the index.php part of the URL
            /*'showScriptName'=>false,*/
            'rules'=>array(
                '<controller:\w+>/<id:\d+>'=>'<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
                '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
            ),
        ),
        
        // This database is shared between the daemons and the control panel
        'bridgeDb'=>array(
            'class' => 'DaemonDbConnection',
            'connectionString' => @$config['daemon_db'],
            'username' => @$config['daemon_db_user'],
            'password' => @$config['daemon_db_pass'],
            'emulatePrepare' => true,
            'charset' => 'utf8',
            'schemaCachingDuration' => '3600',
        ),
    
        // This database saves control panel specific settings
        'db'=>array(
            'class' => 'PanelDbConnection',
            'connectionString' => @$config['panel_db'],
            'username' => @$config['panel_db_user'],
            'password' => @$config['panel_db_pass'],
            'emulatePrepare' => true,
            'charset' => 'utf8',
            'schemaCachingDuration' => '3600',
        ),
        'errorHandler'=>array(
            'errorAction'=>'site/error',
        ),
        'log'=>array(
            'class'=>'CLogRouter',
            'routes'=>array(
                array(
                    'class'=>'CFileLogRoute',
                    'levels'=>'info, error, warning',
                ),
                // uncomment the following to show log messages on web pages
                /*
                array(
                    'class'=>'CWebLogRoute',
                ),
                */
            ),
        ),
        'cache'=>array(
            'class'=>(@$config['sqlitecache_schema'] ? 'CDbCache' : 'CDummyCache'),
        ),
        'mail'=>array(
            'class'=>'ext.yii-mail.YiiMail',
            'logging'=>false,
            'transportType'=>'php',
            // uncomment the following to use an SMTP server for sending emails
            /*
            'transportType'=>'smtp',
            'transportOptions'=>array(
                'host'=>'smtp.example.org',
                'username'=>'mail_user',
                'password'=>'mail_password',
                //'port'=>465,
                //'encryption'=>'ssl',
            ),
            */
            'viewPath'=>'application.views.mail',
            'dryRun'=>false,
        ),
        'themeManager'=>array(
            'themeClass'=>'Theme',
        ),
    ),

    'theme'=>@$config['theme'],
    'language'=>@$config['language'],

    'params'=>$params,
);
