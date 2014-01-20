<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class DaemonController extends Controller
{
    public $layout = '//layouts/column2';


    public function filters()
    {
        return array(
            'accessControl',
        );
    }

    public function accessRules()
    {
        return array(
            array('allow',
                'expression'=>'$user->isSuperuser()',
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    public function actionIndex()
    {
        $settings= array();
        $settings['saveInterval'] =
            array('label'=>Yii::t('admin', 'Autosave Interval (0 to disable)'), 'unit'=>'s', 'factor'=>1000, 'default'=>600);
        $settings['maxChatLines'] =
            array('label'=>Yii::t('admin', 'Number of lines to store in chat window'), 'unit'=>'', 'default'=>120);
        $settings['maxLogLines'] =
            array('label'=>Yii::t('admin', 'Number of lines to store in log/console window'), 'unit'=>'', 'default'=>120);
        $settings['keepBackupCount'] =
            array('label'=>Yii::t('admin', 'Number of backups to keep'), 'unit'=>'', 'default'=>3);
        $settings['serversPerPage'] =
            array('label'=>Yii::t('admin', 'Number of servers to display at once on the serverlist'), 'unit'=>'', 'default'=>10);
        $settings['defaultServerName'] =
            array('label'=>Yii::t('admin', 'Default server name'), 'unit'=>'', 'default'=>'Minecraft Server');
        $settings['defaultServerPlayers'] =
            array('label'=>Yii::t('admin', 'Default number of player slots'), 'unit'=>'', 'default'=>8);
        $settings['defaultServerMemory'] =
            array('label'=>Yii::t('admin', 'Default amount of memory'), 'unit'=>'MB', 'default'=>1024);
        $settings['defaultServerIp'] =
            array('label'=>Yii::t('admin', 'Default server IP'), 'unit'=>array(0=>Yii::t('admin', 'All interfaces (0.0.0.0)'), 1=>Yii::t('admin', 'Daemon IP'), 2=>Yii::t('admin', 'Daemon FTP Server IP')), 'default'=>0);
        $settings['defaultServerPort'] =
            array('label'=>Yii::t('admin', 'Base server port to use on new IPs'), 'unit'=>'', 'default'=>25565);
        $settings['defaultServerStartMemory'] =
            array('label'=>Yii::t('admin', 'Default amount of startup memory'), 'unit'=>'MB', 'default'=>0, 'adv'=>true);
        $settings['updateChecks'] =
            array('label'=>Yii::t('admin', 'Check for Multicraft updates'), 'unit'=>'bool', 'default'=>1, 'adv'=>true);
        $settings['anonStats'] =
            array('label'=>Yii::t('admin', 'Anonymous usage statistics'), 'unit'=>'bool', 'default'=>1, 'adv'=>true);
        $settings['pingInterval'] =
            array('label'=>Yii::t('admin', 'Minecraft Crash Check Interval'), 'unit'=>'s', 'factor'=>1000, 'default'=>30, 'adv'=>true);
        $settings['pingTimeout'] =
            array('label'=>Yii::t('admin', 'Minecraft Response Timeout (0 to disable)'), 'unit'=>'s', 'factor'=>1000, 'default'=>0, 'adv'=>true);
        $settings['crashRestartDelay'] =
            array('label'=>Yii::t('admin', 'Crashed Server Restart Delay (0 to disable)'), 'unit'=>'s', 'factor'=>1000, 'default'=>5, 'adv'=>true);
        $settings['userSaveDelay'] =
            array('label'=>Yii::t('admin', 'Minimum time between two world saves'), 'unit'=>'s', 'factor'=>1000, 'default'=>120, 'adv'=>true);
        $settings['userBackupDelay'] =
            array('label'=>Yii::t('admin', 'Minimum time between two world backups'), 'unit'=>'s', 'factor'=>1000, 'default'=>300, 'adv'=>true);
        $settings['resourceCheckInterval'] =
            array('label'=>Yii::t('admin', 'Minimum time between two resource checks (0 to disable)'), 'unit'=>'s', 'factor'=>1000, 'default'=>1, 'adv'=>true);
        $settings['pongMode'] =
            array('label'=>Yii::t('admin', 'Assume Minecraft is still running on:'), 'unit'=>array(0=>Yii::t('admin', 'Known console output'), 1=>Yii::t('admin', '"List" command output'), 2=>Yii::t('admin', 'Any console output')), 'default'=>0, 'adv'=>true);
        $settings['rateLimit'] =
            array('label'=>Yii::t('admin', 'Limit number of console lines per second to'), 'unit'=>'', 'default'=>30, 'adv'=>true);
        $settings['pluginScanDelay'] =
            array('label'=>Yii::t('admin', 'Plugin repository refresh interval'), 'unit'=>'s', 'factor'=>1000, 'default'=>5, 'adv'=>true);
        $settings['savePlayerInfo'] =
            array('label'=>Yii::t('admin', 'Save player information (ip, lastseen, etc.)'), 'unit'=>array(2=>Yii::t('admin', 'Always Save'), 1=>Yii::t('admin', 'Update Existing'), 0=>Yii::t('admin', 'Never Save')), 'default'=>2, 'adv'=>true);
        
        if (isset($_POST['submit']) && $_POST['submit'] === 'true')
        {
            foreach (array_keys($settings) as $s)
            {
                $value = @$_POST['Setting'][$s];
                $model = Setting::model()->findByPk($s);
                if (!$model)
                {
                    $model = new Setting();
                    $model->key = $s;
                }
                $f = isset($settings[$s]['factor']) ? $settings[$s]['factor'] : 0;
                $value = $f ? $value * $f: $value;
                if (!$value && $value !== "0")
                    $value = '';
                
                if ($value != $model->value)
                    Yii::log(array('update', $model, '"'.$value.'"'));
                $model->value = $value;
                $model->save();
            }
            Yii::log('Updated global settings');
            $this->redirect(array('index'));
        }
    
        foreach (array_keys($settings) as $s)
        {
            $f = isset($settings[$s]['factor']) ? $settings[$s]['factor'] : 0;
            $model = Setting::model()->findByPk($s);
            $val = '';
            if (!$model)
                $val = $settings[$s]['default'];
            else
                $val = $f ? $model->value / $f : $model->value;
            $settings[$s]['value'] = $val;
        }


        $this->render('index',array(
            'settings'=>$settings,
        ));
    }

    public function ajaxAction($cmd, $dmn)
    {
        if ($dmn)
        {
            $ret = McBridge::get()->cmd($dmn, $cmd);
            if (!$ret['success'])
                echo  CHtml::encode(Yii::t('admin', 'Daemon').' '.$dmn.': '.$ret['error']."\n");
        }
        else
        {
            $ret = McBridge::get()->globalCmd($cmd);
            foreach ($ret as $id=>$r)
                if (!$r['success'])
                    echo  CHtml::encode(Yii::t('admin', 'Daemon').' '.$id.': '.$r['error']."\n");
        }
    }

    public function actionUpdateMC()
    {
        $jars = array();
        if (isset($_POST['ajax']))
        {
            switch($_POST['ajax'])
            {
            case 'start':
                if (!in_array($_POST['file'], array('both', 'jar', 'conf')))
                    die(Yii::t('admin', 'Please choose a file type to download.'));
                if ($_POST['file'] == 'both')
                    $_POST['file'] = '';
                $cmd = 'updatejar start :'.$_POST['file'].':'.$_POST['target'];
                $this->ajaxAction($cmd, @$_POST['daemon']);
                Yii::log('Starting download of JAR file '.$_POST['file']);
                break;
            case 'install':
                $cmd = 'updatejar install :1';
                $this->ajaxAction($cmd, @$_POST['daemon']);
                Yii::log('Installing JAR file '.$_POST['file']);
                break;
            }
            Yii::app()->end();
        }
        else if (!isset($_GET['ajax'])) //CListUpdate request
        {
            $ret = McBridge::get()->globalCmd('updatejar list :');

            $jars = array();
            foreach ($ret as $id=>$r)
            {
                if (!$r['success'])
                    continue;
                 
                foreach ($r['data'] as $jar)
                    $jars[$jar['jar']] = $jar['name'];
            }
            natcasesort($jars);
        }

        $file = array(
            ''=>Yii::t('admin', 'Select'),
            'conf'=>Yii::t('admin', '.conf File'),
            'jar'=>Yii::t('admin', 'JAR File'),
            'both'=>Yii::t('admin', 'Both Files'),
        );
        
        $this->render('updateMC',array(
            'daemonList'=>new CActiveDataProvider('Daemon', array(
                'criteria'=>array(
                    'order'=>'id ASC',
                ),
                'pagination'=>array(
                    'pageSize'=>5
                ),
            )),
            'jars'=>$jars,
            'file'=>$file,
        ));
    }

    public function runCmd($dmn, $cmd)
    {
        $errors = array();
        if ($dmn)
        {
            Yii::log('Running command "'.$cmd.'" on daemon '.$dmn);
            $ret = McBridge::get()->cmd($dmn, $cmd);
            if (!$ret['success'])
                $errors[] = CHtml::encode(Yii::t('admin', 'Daemon').' '.$dmn.': '.$ret['error']);
        }
        else
        {
            Yii::log('Running command "'.$cmd.'" on all daemons');
            $ret = McBridge::get()->globalCmd($cmd);
            foreach ($ret as $id=>$r)
                if (!$r['success'])
                    $errors[] =  CHtml::encode(Yii::t('admin', 'Daemon').' '.$id.': '.$r['error']);
        }
        if (count($errors))
            Yii::app()->user->setFlash('files-error', join("<br/>", $errors));
        else
            Yii::app()->user->setFlash('files-success', Yii::t('admin', 'Command successfully sent.'));
        //$this->redirect(array('files', 'daemon_id'=>$dmn));
    }

    public function filesFail($dmn, $error)
    {
        Yii::app()->user->setFlash('files-error', CHtml::encode($error));
    }

    public function actionFiles($daemon_id = 0)
    {
        $dmn = (int)(isset($_POST['daemon_id']) ? $_POST['daemon_id'] : $daemon_id);
        $errors = array();
        $cmd = false;
        if (isset($_POST['do_download']))
        {
            $target = $_POST['download-target'];
            if (!preg_match("/^[^\\/?*:;{}\\\n]+$/", $target))
                $this->filesFail($dmn, Yii::t('admin', 'Invalid file name specified.'));
            else
            {
                $protocols = '/^(ftp|ftps|http|https):\/\//';
                $file = $_POST['download-file'];
                $conf = $_POST['download-conf'];
                if ($file && !preg_match($protocols, $file))
                    $file = 'http://'.$file;
                if ($conf && !preg_match($protocols, $conf))
                    $conf = 'http://'.$conf;
                Yii::log('Starting download of JAR file '.$target.' from "'.$file.'", conf "'.$conf.'"');
                $this->runCmd($dmn, 'downloadjar '.$target.' :'.$file.' :'.$conf);
            }
        }
        else if (isset($_POST['do_delete']))
        {
            $target = $_POST['delete-target'];
            $file = $_POST['delete-file'];
            if (!preg_match("/^[^\\/?*:;{}\\\n]+$/", $target))
                $this->filesFail($dmn, Yii::t('admin', 'Invalid file name specified.'));
            else if (!in_array($file, array('both', 'file', 'conf')))
                $this->filesFail($dmn, Yii::t('admin', 'Please choose a file type to delete.'));
            else
            {
                Yii::log('Deleting JAR file '.$target.' ('.$file.')');
                $this->runCmd($dmn, 'deletejar '.$target.' :'.$file);
            }
        }

        $ret = McBridge::get()->globalCmd('updatejar list :');

        $jars = array();
        foreach ($ret as $id=>$r)
        {
            if (!$r['success'])
                continue;
             
            foreach ($r['data'] as $jar)
                $jars[$jar['jar']] = $jar['jar'].' ('.$jar['name'].')';
        }
        natcasesort($jars);

        $this->render('files',array(
            'daemon_id'=>$dmn,
            'jars'=>$jars,
        ));
    }

    public function actionStatus($id = 0)
    {
        if (isset($_POST['ajax']))
            Yii::app()->end();
                
        $this->render('status',array(
            'daemonList'=>new CActiveDataProvider('Daemon', array(
                'criteria'=>array(
                    'condition'=>($id ? 'id='.((int)$id) : ''),
                    'order'=>'id ASC',
                ),
                'pagination'=>array(
                    'pageSize'=>5
                ),
            )),
        ));
    }

    private function runServerAction($sv, $action, $params)
    {
        switch ($action)
        {
        case 'active_start':
            if (!McBridge::get()->serverCmd($sv->id, 'start'))
                return array(false, McBridge::get()->lastError());
            break;
        case 'active_stop':
            if (!McBridge::get()->serverCmd($sv->id, 'stop'))
                return array(false, McBridge::get()->lastError());
            break;
        case 'active_restart':
            if (!McBridge::get()->serverCmd($sv->id, 'restart'))
                return array(false, McBridge::get()->lastError());
            break;
        case 'active_suspend':
            if (!$sv->suspend())
                return array(false, CHtml::errorSummary($sv));
            else
                McBridge::get()->serverCmd($sv->id, 'stop');
            break;
        case 'suspended_resume':
            if (!$sv->resume())
                return array(false, CHtml::errorSummary($sv));
            break;
        default:
            return array(false, Yii::t('admin', 'Unknown action "{action}"', array('{action}'=>$action)));
            break;
        }
        return array(true, '');
    }

    public function actionOperations($id = 0)
    {
        if (isset($_POST['daemon_id']))
            $id = $_POST['daemon_id'];
        $all = ($id === 'all');
        $did = (int)$id;
        $action = false;
        $params = array();
        $acts = array(
            'active_start',
            'active_stop',
            'active_restart',
            'active_suspend',
            'suspended_resume',
            'run_chat',
            'run_stop',
            'run_restart',
            'run_console',
            'global_clean_players',
            'global_clear_cmdcache',
        );
        foreach ($_POST as $k=>$v)
        {
            if (in_array($k, $acts))
            {
                $action = $k;
                break;
            }
            $params[$k] = $v;
        }
        if (($did || $all) && preg_match('/^run_/', $action))
        {
            $cmd = substr($action, 4);
            if ($action == 'run_console')
            {
                if (!strlen(@$params['command']))
                {
                    Yii::app()->user->setFlash('operations', Yii::t('admin', 'No command to send.'));
                    $this->redirect(array('operations', 'id'=>$id));
                }
                $cmd = 'run_s:'.$params['command'];
            }
            else if ($action == 'run_chat')
            {
                if (!strlen(@$params['message']))
                {
                    Yii::app()->user->setFlash('operations', Yii::t('admin', 'No message to send.'));
                    $this->redirect(array('operations', 'id'=>$id));
                }
                $from = strlen(@$params['from']) ? $params['from'] : Yii::app()->user->name;
                $cmd = 'mc:say <'.$from.'> '.$params['message'];
            }
            if ($all)
                $res = McBridge::get()->globalCmd('server running:'.$cmd);
            else
                $res = array($did=>McBridge::get()->cmd($did, 'server running:'.$cmd));
            $msg = '';
            $runIds = array();
            $failIds = array();
            foreach ($res as $i=>$r)
            {
                if (@$r['success'])
                    $runIds[] = $i;
                else
                    $failIds[] = $i.': '.CHtml::encode(@$r['error']);
            }
            $msg = '';
            if (count($runIds))
                $msg .= Yii::t('admin', 'Action run for daemons:').'<br/>'.implode(', ', $runIds);
            if (count($failIds))
                $msg .= (count($runIds) ? '<br/><br/>' : '').Yii::t('admin', 'Action failed for daemons:').'<br/>'.implode(', ', $failIds);
            if (!strlen($msg))
                $msg = Yii::t('admin', 'No daemons affected');
            Yii::app()->user->setFlash('operations', $msg);
            $this->redirect(array('operations', 'id'=>$id) + $params);
        }
        else if (($did || $all) && preg_match('/^(active_|suspended_)/', $action))
        {
            $active = false;
            if (preg_match('/^active_/', $action))
                $active = true;
            $cond = array('suspended'=>($active ? 0 : 1));
            if (!$all)
                $cond['daemon_id'] = $did;
            $svs = Server::model()->findAllByAttributes($cond);

            $runIds = array();
            $failIds = array();

            foreach ($svs as $sv)
            {
                $res = $this->runServerAction($sv, $action, $params);
                if (!$res[0])
                    $failIds[] = $sv->id.': '.$res[1];
                else
                    $runIds[] = $sv->id;
            }
            $msg = '';
            if (count($runIds))
                $msg .= Yii::t('admin', 'Action run for servers:').'<br/>'.implode(', ', $runIds);
            if (count($failIds))
                $msg .= (count($runIds) ? '<br/><br/>' : '').Yii::t('admin', 'Action failed for servers:').'<br/>'.implode(', ', $failIds);
            if (!strlen($msg))
                $msg = Yii::t('admin', 'No servers affected');
            Yii::app()->user->setFlash('operations', $msg);
            $this->redirect(array('operations', 'id'=>$id) + $params);
        }
        else if (preg_match('/^global_/', $action))
        {
            if ($action == 'global_clean_players')
            {
                $sql = 'delete from `player` where `level`=1 or `level`=(select `default_level`'
                    .' from `server` where `id`=`server_id`)';
                $cmd = Yii::app()->bridgeDb->createCommand($sql);
                $del = $cmd->execute();
                Yii::log('Player table cleanup: Deleted '.$del.' player entries');
                Yii::app()->user->setFlash('operations', Yii::t('admin', 'Deleted {del} player entries.',
                    array('{del}'=>$del)));
                $this->redirect(array('operations', 'id'=>$id));
            }
            else if ($action == 'global_clear_cmdcache')
            {
                CommandCache::clear();
                Yii::log('Cleared command cache table');
                Yii::app()->user->setFlash('operations', Yii::t('admin', 'Cleared command cache table.'));
                $this->redirect(array('operations', 'id'=>$id));
            }
        }
                
        $this->render('operations',array('daemon_id'=>$id));
    }


    public function actionRemoveDaemon($id)
    {
        Daemon::model()->deleteByPk($id);
        $this->redirect(array('status'));
    }

    public function actionStatistics()
    {
        if (isset($_POST['ajax']))
        {
            if ($_POST['ajax'] === 'stats')
            {
                $svs = Server::model()->findAllByAttributes(array('suspended'=>0));
                $players = 0;
                $servers = 0;
                $memory = 0;
                foreach ($svs as $sv)
                {
                    $pl = $sv->getOnlinePlayers();
                    if ($pl >= 0)
                    {
                        $servers++;
                        $players += $pl;
                        $memory += $sv->memory;
                    }
                }
                $data = array();
                $data['servers'] = $servers;
                $data['players'] = $players;
                $data['avg_players'] = $servers ? number_format($players / $servers, 2) : 0;
                $data['memory'] = number_format($memory).' '.Yii::t('admin', 'MB');
                echo CJSON::encode($data);
            }
            Yii::app()->end();
        }

        $sql = 'select count(*), sum(`players`), sum(`memory`) from `server`';
        $cmd = Yii::app()->bridgeDb->createCommand($sql);
        $row = $cmd->queryRow(false);
        $servers = $row[0];
        $players = $row[1];
        $memory = $row[2];

        $sql = 'select count(*), sum(`players`), sum(`memory`) from `server` where `suspended`!=1';
        $cmd = Yii::app()->bridgeDb->createCommand($sql);
        $row = $cmd->queryRow(false);
        $activeServers = $row[0];
        $activePlayers = $row[1];
        $activeMemory = $row[2];

        $sql = 'select count(*) from `daemon`';
        $cmd = Yii::app()->bridgeDb->createCommand($sql);
        $dmns = $cmd->queryScalar();

        
        $this->render('statistics',array(
            'servers' => $servers,
            'activeServers' => $activeServers,
            'daemons' => $dmns,
            'svPerDaemon' => ($dmns ? ($servers / $dmns) : 0),
            'activeSvPerDaemon' => ($dmns ? ($activeServers / $dmns) : 0),
            'slots' => $players,
            'activeSlots' => $activePlayers,
            'memory' => number_format($memory).' '.Yii::t('admin', 'MB'),
            'activeMemory' => number_format($activeMemory).' '.Yii::t('admin', 'MB'),
        ));
    }

    public function saveCfg($p)
    {
        $header = '<?php /*** THIS FILE WAS GENERATED BY THE MULTICRAFT FRONT-END ***/'."\n"
            .'return ';
        if (!isset($p['config']['panel_db'])
            || !isset($p['config']['daemon_db']))
            throw CHttpException(500, 'Config file is missing critical settings.');
        $content = $header.var_export($p['config'], true).';';
        return file_put_contents($p['config_file'], $content);
    }

    public function actionPanelConfig()
    {
        $p = array();
        $p['config_file'] = realpath(dirname(__FILE__).'/../config/').'/config.php';
        $cfg = require($p['config_file']);
        if (!is_array($cfg))
            $cfg = array();
        $p['config'] = $cfg;

        if (isset($_POST['submit_settings']))
        {
            foreach ($_POST['settings'] as $k=>$v)
            {
                if ($v == 'sel_true')
                    $v = true;
                else if ($v == 'sel_false')
                    $v = false;

                if ($v != @$p['config'][$k])
                    Yii::log('Panel setting "'.$k.'" changed to "'.$v.'"');

                $p['config'][$k] = $v;
            }
            //safety override
            $p['config']['panel_db'] = $cfg['panel_db'];
            $p['config']['panel_db_user'] = (string)@$cfg['panel_db_user'];
            $p['config']['panel_db_pass'] = (string)@$cfg['panel_db_pass'];
            $p['config']['daemon_db'] = $cfg['daemon_db'];
            $p['config']['daemon_db_user'] = (string)@$cfg['daemon_db_user'];
            $p['config']['daemon_db_pass'] = (string)@$cfg['daemon_db_pass'];
            $p['config']['daemon_password'] = $cfg['daemon_password'];
            Yii::log('Updating panel configuration');
            if (Yii::app()->params['demo_mode'] == 'enabled')
                Yii::app()->user->setFlash('panel_config', Yii::t('admin', 'Function disabled in demo mode.'));
            else if(!$this->saveCfg($p))
                Yii::app()->user->setFlash('panel_config', Yii::t('admin', 'Failed to save settings.'));
            $this->redirect(array('panelConfig'));
        }

        $this->render('panelConfig',array(
            'p' => $p,
        ));
    }

    static function getDaemonStatus($daemon)
    {
        $ret = McBridge::get()->cmd($daemon, 'version');
        $status = array();
        $status['class'] = 'flash-success';
        if (!$ret['success'])
        {
            $status['version'] = $status['remote'] = Yii::t('admin', 'Unknown');
            $status['class'] = 'flash-error';
            $status['time'] = Yii::t('admin', 'Failed to get version:').' '.CHtml::encode($ret['error']);
        }
        else
        {
            $data = $ret['data'][0];
            if ($data['time'])
                $status['time'] = @date('Y-m-d, H:i', $data['time']);
            else
                $status['time'] = 'None';
            $status['version'] = $data['version'];
            $status['remote'] = $data['remote'];
            $status['info'] = @$data['info'];
            if ($status['remote'] != 'Unknown' && $status['version'] != $status['remote'])
                $status['class'] = 'flash-notice';
        }
        return $status;
    }

    public function getJarStatus($daemon)
    {
        $ret = McBridge::get()->cmd($daemon, 'updatejar status :1');

        $content = '';   
        $time = '';   
        $class = 'flash-success';
        $fails = 0;
        if (!$ret['success'])
        {
            $content = CHtml::encode($ret['error']);
            $class = 'flash-error';
        }
        else
        {
            $data = $ret['data'];
            if (!is_array($data) || !count($data))
                $data = array('time'=>time());
            foreach ($data as $d)
            {
                if ($content)
                    $content .= '<br/>';
                $time = @$d['time'] ? '['.@date('m/d H:i', $d['time']).'] ' : '';
                if (isset($d['target']))
                    $content .= '<b>'.$d['target'].'</b>: ';
                switch (@$d['status'])
                {
                case 'done':
                    $content .= Yii::t('admin', 'Update successful');
                    break;
                case 'uptodate':
                    $content .= Yii::t('admin', 'No update necessary');
                    break;
                case 'ready':
                    $content .= Yii::t('admin', 'The update is ready to be installed');
                    break;
                case 'running':
                    $content .= Yii::t('admin', 'Downloading, please wait {percent}', array('{percent}'=>($d['percent'] ?
                            ' '.(int)((float)$d['percent'] * 100).'%' : '')));
                    break;
                default:
                    if (@$d['message'])
                    {
                        $fails++;
                        $content .= htmlspecialchars($d['message']);
                    }
                    else
                        $content .= Yii::t('admin', 'No update in progress.');
                }
            }
            if ($fails == count($data))
                $class = 'flash-error';
            else if ($fails)
                $class = 'flash-notice';
        }
        $status = array();
        $status['class'] = $class;
        $status['time'] = $time;
        $status['content'] = $content;
        return $status;
    }
}
