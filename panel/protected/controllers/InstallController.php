<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class InstallController extends Controller
{
    static $steps = array(
        'welcome',
        'requirements',
        'config',
        'panel',
        'daemon',
        'settings',
        'connection',
        'done',
    );
    static $stepLabels = array(
        'Welcome',
        'Requirements Check',
        'Configuration File',
        'Database 1: PANEL&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
        'Database 2: DAEMON&nbsp;&nbsp;',
        'Settings',
        'Daemon Connection',
        'Installation Complete',
    );

    var $p = array('actions'=>array());

    public function run($a)
    {
        if (@Yii::app()->params['installer'] !== 'show')
            return $this->missingAction($a);
        return parent::run($a);
    }

    public function actionIndex($step = '')
    {
        $idx = array_search($step, InstallController::$steps);
        if ($idx === false)
            $idx = 0;
        $prevStep = '';
        $step = 'welcome';
        $this->p['success'] = true;
        for ($i = 0; $i <= $idx; $i++)
        {
            $prevStep = $step;
            $step = InstallController::$steps[$i];
            $func = 'check_'.$step;
            $this->p['actions'] = array();
            if (!$this->$func())
            {
                $idx = $i;
                $this->p['success'] = false;
                break;
            }
        }
        
        $this->render('index',array(
            'step'=>$step,
            'prevStep'=>$prevStep,
            'idx'=>$idx,
            'p'=>$this->p,
        ));
    }

    public function check_welcome()
    {
        return true;
    }

    public function check_requirements()
    {
        function t($category,$message,$params=array())
        {
            return $message;
        }

function checkProtectedDirectory()
{
    $testfile = Yii::app()->getRequest()->getHostInfo().Yii::app()->getBaseUrl().'/protected/data/daemon/schema.mysql.sql';
    $f = @file_get_contents($testfile);
    if (strlen($f))
        return 'The "protected" directory of your front end must not be accessible through the webserver, please see:<br/><a href="http://multicraft.org/site/page?view=troubleshooting#protected_accessible" target="_blank">Troubleshooting: "protected" directory</a>';
    return '';
}

//BEGIN YII REQUIREMENTS CHECK SCRIPT
/**
 * Yii Requirement Checker script
 *
 * This script will check if your system meets the requirements for running
 * Yii-powered Web applications.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @version $Id: index.php 2986 2011-02-20 17:08:50Z alexander.makarow $
 * @package system
 * @since 1.0
 */
function checkServerVar()
{
    $vars=array('HTTP_HOST','SERVER_NAME','SERVER_PORT','SCRIPT_NAME','SCRIPT_FILENAME','PHP_SELF','HTTP_ACCEPT','HTTP_USER_AGENT');
    $missing=array();
    foreach($vars as $var)
    {
        if(!isset($_SERVER[$var]))
            $missing[]=$var;
    }
    if(!empty($missing))
        return t('yii','$_SERVER does not have {vars}.',array('{vars}'=>implode(', ',$missing)));

    if(realpath($_SERVER["SCRIPT_FILENAME"]) !== realpath(dirname(__FILE__).'/../../install.php'))
        return t('yii','$_SERVER["SCRIPT_FILENAME"] must be the same as the entry script file path.');

    if(!isset($_SERVER["REQUEST_URI"]) && isset($_SERVER["QUERY_STRING"]))
        return t('yii','Either $_SERVER["REQUEST_URI"] or $_SERVER["QUERY_STRING"] must exist.');

    if(!isset($_SERVER["PATH_INFO"]) && strpos($_SERVER["PHP_SELF"],$_SERVER["SCRIPT_NAME"]) !== 0)
        return t('yii','Unable to determine URL path info. Please make sure $_SERVER["PATH_INFO"] (or $_SERVER["PHP_SELF"] and $_SERVER["SCRIPT_NAME"]) contains proper value.');

    return '';
}

function checkGD()
{
    if(extension_loaded('gd'))
    {
        $gdinfo=gd_info();
        if($gdinfo['FreeType Support'])
            return '';
        return t('yii','GD installed<br />FreeType support not installed');
    }
    return t('yii','GD not installed');
}

/**
 * @var array List of requirements (name, required or not, result, used by, memo)
 */
$requirements=array(
    array(
        t('yii','PHP version'),
        true,
        version_compare(PHP_VERSION,"5.1.0",">="),
        '<a href="http://www.yiiframework.com">Yii Framework</a>',
        t('yii','PHP 5.1.0 or higher is required.')),
    array(
        t('yii','$_SERVER variable'),
        true,
        ($message=checkServerVar()) === '',
        '<a href="http://www.yiiframework.com">Yii Framework</a>',
        $message),
    array(
        t('yii','Reflection extension'),
        true,
        class_exists('Reflection',false),
        '<a href="http://www.yiiframework.com">Yii Framework</a>',
        ''),
    array(
        t('yii','PCRE extension'),
        true,
        extension_loaded("pcre"),
        '<a href="http://www.yiiframework.com">Yii Framework</a>',
        ''),
    array(
        t('yii','SPL extension'),
        true,
        extension_loaded("SPL"),
        '<a href="http://www.yiiframework.com">Yii Framework</a>',
        ''),
    array(
        t('yii','Protected directory'),
        true,
        ($message=checkProtectedDirectory()) === '',
        'System Security',
        $message),
    array(
        t('yii','PDO extension'),
        true,
        extension_loaded('pdo'),
        t('mc','All database connections'),
        t('mc', 'The PHP PDO extension is always required.')),
    array(
        t('yii','PDO SQLite extension'),
        false,
        extension_loaded('pdo_sqlite'),
        t('yii','SQLite Database connection'),
        t('yii','This is required if you are using an SQLite database.')),
    array(
        t('yii','PDO MySQL extension'),
        false,
        extension_loaded('pdo_mysql'),
        t('yii','MySQL database connection'),
        t('yii','This is required if you are using a MySQL database.')),
    array(
        t('yii','GD extension with<br />FreeType support'),
        false,
        ($message=checkGD()) === '',
        //extension_loaded('gd'),
        t('mc', 'Multicraft status banner and registration captcha'),
        t('mc', 'Only required if you want to use the status banners or registration captcha.').' '.$message),
);

$result=1;  // 1: all pass, 0: fail, -1: pass with warnings

foreach($requirements as $i=>$requirement)
{
    if($requirement[1] && !$requirement[2])
        $result=0;
    else if($result > 0 && !$requirement[1] && !$requirement[2])
        $result=-1;
    if($requirement[4] === '')
        $requirements[$i][4]='&nbsp;';
}
//END YII REQUIREMENTS CHECK SCRIPT

        if ($result > 0)
            $this->p['actions'][] = 'All requirements met';
        else if ($result < 0)
            $this->p['actions'][] = 'Minimum requirements met, some functionality may be unavailable';
        else
            $this->p['actions'][] = 'Minumum requirements not met';

        $this->p['requirements'] = $requirements;
        $this->p['result'] = $result;
        return true;
    }

    public function loadCfg()
    {
        $cfg = require($this->p['config_file']);
        if (!is_array($cfg))
            $cfg = array();
        $this->p['config'] = $cfg;
    }

    public function saveCfg()
    {
        $header = '<?php /*** THIS FILE WAS GENERATED BY THE MULTICRAFT INSTALLER ***/'."\n"
            .'return ';
        return file_put_contents($this->p['config_file'], $header.var_export($this->p['config'], true).';');
    }

    public function check_config()
    {
        $file = realpath(dirname(__FILE__).'/../config/').'/config.php';
        $this->p['config_file'] = $file;
        $this->p['config_exists'] = @file_exists($file);
        $valid = true;
        if (!$this->p['config_exists'])
        {
            $this->p['actions'][] = 'Trying to copy "config.php.dist" to "config.php" in "'.realpath(dirname(__FILE__).'/../config/').'"';
            if (!@copy($file.'.dist', $file))
            {
                $this->p['actions'][] = 'Failed to copy file, please copy the file manually and make sure it is writeable by the webserver';
                $this->p['config_exists'] = false;
                $valid = false;
            }
            else
            {
                $this->p['actions'][] = 'File copied successfully';
                $this->p['config_exists'] = true;
            }
        }
        else
        {
            $this->p['actions'][] = 'Configuration file found';
        }
        if (!@is_writeable($file))
        {
            $this->p['actions'][] = 'Configuration file is not writeable!';
            $this->p['config_writeable'] = false;
            $valid = false;
        }
        else
            $this->p['config_writeable'] = true;
        if ($valid)
            $this->loadCfg();
        return $valid;
    }

    public function check_panel()
    {
        $checks = $this->check_db_common('panel');
        if ($checks && !Yii::app()->user->isSuperuser())
            return false;
        $this->p['actions'] = array();
        return $this->check_db_common('panel', false);
    }

    public function check_daemon()
    {
        return $this->check_db_common('daemon', false);
    }

    public function check_db_panel()
    {
        if (Yii::app()->cache)
            Yii::app()->cache->flush();
        try
        {
            $cmd = $this->p['panel_db']->createCommand('select count(*) from `server_config`')->execute();
            $cmd = $this->p['panel_db']->createCommand('select count(*) from `user_player`')->execute();
            $cmd = $this->p['panel_db']->createCommand('select count(*) from `user_server`')->execute();
            $cmd = $this->p['panel_db']->createCommand('select count(*) from `user`');
            $users = $cmd->queryScalar();
            $cmd = $this->p['panel_db']->createCommand('select count(*) from `user` where `password`!=\'\' and (length(`password`)!=34 or `password` not like \'$1$________$%\')');
            $wrongPw = $cmd->queryScalar();
        }
        catch(Exception $e)
        {
            $this->p['actions'][] = 'Error querying user table: '.$e->getMessage();
            return false;
        }
        if (!@$users)
        {
            $this->p['actions'][] = 'Error: User table needs to contain at leas one user';
            return false;
        }
        if (@$wrongPw)
        {
            $this->p['actions'][] = '<b>WARNING</b>: The user table contains passwords in a wrong format, this can be a security risk!<br/>Please verify that the passwords are encrypted in your database and contact Multicraft support for further help.';
            $this->p['pw_issue'] = true;
            return true;
        }
        return true;
    }

    public function check_db_daemon()
    {
        if (Yii::app()->cache)
            Yii::app()->cache->flush();
        try
        {
            $cmd = $this->p['daemon_db']->createCommand('select count(*) from `server`')->execute();
            $cmd = $this->p['daemon_db']->createCommand('select count(*) from `player`')->execute();
            $cmd = $this->p['daemon_db']->createCommand('select count(*) from `command`')->execute();
            $cmd = $this->p['daemon_db']->createCommand('select count(*) from `daemon`')->execute();
            $cmd = $this->p['daemon_db']->createCommand('select count(*) from `setting`');
            $settings = $cmd->queryScalar();
        }
        catch(Exception $e)
        {
            $this->p['actions'][] = 'Error querying setting table: '.$e->getMessage();
            return false;
        }
        return true;
    }

    public function check_db_common($type, $ro = true)
    {
        $res = false;
        $cfg = &$this->p['config'];

        if (!$ro && @$_POST['submit_'.$type.'_db'] === 'true')
        {
            $this->p['actions'][] = 'Setting new DB information';
            $driver = $_POST['driver'];
            $str = '';
            switch ($driver)
            {
            case 'sqlite':
                $str = 'sqlite:'.$_POST['dbPath'];
                break;
            case 'mysql':
                $str = 'mysql:host='.$_POST['dbHost'].';dbname='.$_POST['dbName'];
                break;
            default:
                $str = $_POST['dbString'];
            }
            $cfg[$type.'_db'] = $str;
            $cfg[$type.'_db_user'] = $_POST['dbUser'];
            $cfg[$type.'_db_pass'] = $_POST['dbPass'];
            $this->saveCfg();
            $this->redirect(array('index', 'step'=>$type));
        }

        $str = @$cfg[$type.'_db'];
        if (preg_match('/sqlite:/', $str))
        {
            $driver = 'sqlite';
            $path = substr($str, strlen('sqlite:'));
        }
        else if (preg_match('/mysql/', $str))
        {
            $driver = 'mysql';
            $m = array();
            preg_match('/mysql:host=([^;]*);dbname=(.+)/', $str, $m);
            if (count($m) > 1)
                $host = $m[1];
            if (count($m) > 2)
                $name = $m[2];
        }
        else if (strlen($str))
            $driver = 'manual';
        else
            $driver = 'sqlite';

        
        $this->p['actions'][] = 'Trying to connect to the '.$type.' database';
        $db = new DbConnection(@$cfg[$type.'_db'], @$cfg[$type.'_db_user'], @$cfg[$type.'_db_pass']);
        $this->p[$type.'_db'] = $db;
        try
        {
            @$db->active = true;
        }
        catch(Exception $e)
        {
            $error = $e->getMessage();
        }
        if ($db->active)
        {
            $this->p[$type.'_db_connected'] = true;
            $this->p['actions'][] = 'Connection successful';

            $check_func = 'check_db_'.$type;
            if (!$this->$check_func())
            {
                $this->p[$type.'_db_initialized'] = false;
                if (!$ro && @$_POST['submit_init_'.$type.'_db'] === 'true')
                {
                    array_pop($this->p['actions']); //remove error message
                    $schema = dirname(__FILE__).'/../data/'.$type.'/schema.'.$driver.'.sql';
                    $this->p['actions'][] = 'Initializing database from: '.$schema;

                    try
                    {
                        $db->executeFile($schema);
                        $this->p[$type.'_db_initialized'] = true;
                    }
                    catch(Exception $e)
                    {
                        $this->p['actions'][] = 'Error Initializing '.$type.' database: '.$e->getMessage();
                    }
                }
                else
                    $this->p['actions'][] = 'Database seems to be uninitialized';

            }
            else
                $this->p[$type.'_db_initialized'] = true;

            if ($this->p[$type.'_db_initialized'])
            {
                $res = true;
                if (!$ro)
                {
                    $db->active = false;
                    $this->p['actions'][] = 'Opening database in production mode';
                    try
                    {
                        if ($type == 'panel')
                            $db = &Yii::app()->db;
                        else
                            $db = &Yii::app()->bridgeDb;
                        @$db->active = true;
                        if ($db->getV() != $db->version)
                            $this->p['actions'][] = 'Applying updates';
                        @$db->dbCheck();                        
                    }
                    catch(Exception $e)
                    {
                        $this->p['actions'][] = 'Error re-opening database: '.$e->getMessage();
                        if (!$ro && @$_POST['submit_ignore_error_'.$type.'_db'] === 'true')
                        {
                            $this->p['actions'][] = 'Trying to force-ignore the errors.';
                            try
                            {
                                @$db->dbCheck(true);
                            }
                            catch(Exception $e)
                            {
                                $this->p['actions'][] = 'Failed to ignore update errors: '.$e->getMessage();
                                $res = false;
                            }
                        }
                        else
                            $res = false;
                    }
                    if ($res)
                        $this->p['actions'][] = 'All done.';
                }
            }
        }
        else
        {
            $this->p[$type.'_db_connected'] = false;
            $this->p['actions'][] = 'Failed to connect: '.$error;
            if (preg_match('/sqlite:/', @$cfg[$type.'_db']))
                $pdo = extension_loaded('pdo_sqlite');
            else if (preg_match('/mysql:/', @$cfg[$type.'_db']))
                $pdo = extension_loaded('pdo_mysql');
            else
                $pdo = false;
            $this->p[$type.'_db_pdo'] = $pdo;
        }

        $this->p[$type.'_db_driver'] = $driver;
        $this->p[$type.'_db_path'] = @$path ? $path : '';
        $this->p[$type.'_db_host'] = @$host ? $host : '127.0.0.1';
        $this->p[$type.'_db_name'] = @$name ? $name : 'multicraft_'.$type;
        return $res;
    }

    public function check_settings()
    {
        if (isset($_POST['submit_settings']))
        {
            foreach ($_POST['settings'] as $k=>$v)
            {
                if ($v == 'sel_true')
                    $v = true;
                else if ($v == 'sel_false')
                    $v = false;
                $this->p['config'][$k] = $v;
            }
            if (!$this->saveCfg())
            {
                $this->p['actions'][] = 'Failed to save settings';
                return false;
            }
            $this->redirect(array('index', 'step'=>'connection'));
        }
        return true;
    }

    public function check_connection()
    {
        $this->p['daemons'] = count(Daemon::model()->findAll());
        return 0 != $this->p['daemons'];
    }

    public function check_done()
    {
        Yii::app()->user->setState('mcUpdateCheck', 0);
        return true;
    }

    public function getDaemonStatus($dmn)
    {
        Yii::import('application.controllers.DaemonController');
        return DaemonController::getDaemonStatus($dmn);
    }

    public function actionRemoveDaemon($id)
    {
        Daemon::model()->deleteByPk($id);
        $this->redirect(array('index', 'step'=>'connection'));
    }
}
