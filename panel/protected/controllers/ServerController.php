<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class ServerController extends Controller
{
    public $layout='//layouts/column2';
    public $mc = null;
    public $ip;

    public function init()
    {
        parent::init();
        $this->ip = $_SERVER['REMOTE_ADDR'];
        $this->ip = preg_replace('/^[^\d]*/', '', $this->ip);
    }

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
                'actions'=>array('index', 'view', 'chat', 'public', 'log'),
                'users'=>array('*'),
            ),
            array('allow',
                'actions'=>array('update', 'users', 'backup', 'restore', 'configs', 'editConfig', 'editPermissionsConfig', 'ftp', 'plugins', 'mysqlDb', 'bgPlugins', 'bgPlugin'),
                'users'=>array('@'),
            ),
            array('allow',
                'actions'=>array('create', 'admin', 'delete', 'suspend', 'resume', 'dismiss'),
                'expression'=>'$user->isSuperuser()',
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }
    private $ownServerSql = 'select `us`.`server_id` from `user_server` as `us` join `server_config` as `sc` on `us`.`server_id`=`sc`.`server_id` where `user_id`=? and (`role`=\'owner\' or (`sc`.`visible`!=0 and `role`!=\'none\' and `role`!=\'\'))';

    private function listJars($id = 0)
    {
        $id = (int)$id;
        if ($id)
            $ret = array($id=>McBridge::get()->cmd(Server::getDaemon($id), 'updatejar list :'.$id));
        else
            $ret = McBridge::get()->globalCmd('updatejar list :');

        $jars = false;
        foreach ($ret as $id=>$r)
        {
            if (!$r['success'])
                continue;
            if (!$jars)
                $jars = array(''=>'Default');
            foreach ($r['data'] as $jar)
                $jars[$jar['jar']] = $jar['name'];
        }
        if ($jars)
            asort($jars);
        return $jars;
    }

    public function actionDismiss($id)
    {
        $sql = 'delete from `move_status` where `server_id`=?';
        $cmd = Yii::app()->bridgeDb->createCommand($sql);
        $cmd->bindValue(1, $id);
        $cmd->execute();
        $this->redirect(array('view','id'=>$id));
    }

    public function actionSuspend($id, $stop = 1)
    {
        $model = $this->loadModel($id);
        if (Yii::app()->params['demo_mode'] == 'enabled')
        {
            Yii::app()->user->setFlash('server', Yii::t('mc', 'Function disabled in demo mode.'));
        }
        else if (!$model->suspend())
        {
            Yii::app()->user->setFlash('server', Yii::t('mc', 'Server suspend failed: ')
                .CHtml::errorSummary($model));
        }
        else
        {
            if ($stop)
                McBridge::get()->serverCmd($id, 'stop');
            Yii::app()->user->setFlash('server', Yii::t('mc', 'Server suspended'));
        }
        $this->redirect(array('view','id'=>$model->id));
    }

    public function actionResume($id, $start = 1)
    {
        $model = $this->loadModel($id);
        if (!$model->resume())
        {
            Yii::app()->user->setFlash('server', Yii::t('mc', 'Server resume failed: ')
                .CHtml::errorSummary($model));
        }
        else
        {
            if ($start)
                McBridge::get()->serverCmd($id, 'start');
            Yii::app()->user->setFlash('server', Yii::t('mc', 'Server resumed'));
        }
        $this->redirect(array('view','id'=>$model->id));
    }

    public function actionView($id)
    {
        Yii::app()->user->can($id, 'view', true);
        $model = $this->loadModel($id);
        $settings = ServerConfig::model()->findByPk((int)$id);
        if (!$settings)
        {
            $settings = new ServerConfig;
            $settings->server_id = $model->id;
        }
        //$this->performAjaxValidation(array($model, $settings));
        $all = array('players', 'buttons', 'status', 'resources', 'movestatus');
        if (isset($_POST['ajax']))
        {
            switch ($_POST['ajax'])
            {
            case 'refresh':
                echo CJSON::encode($this->ajaxRefresh($model, $all));
                break;
            case 'start':
                if (Yii::app()->user->can($id, 'start'))
                {
                    if (!McBridge::get()->serverCmd($id, 'start'))
                        echo McBridge::get()->lastError();
                }
                break;
            case 'stop':
                if (Yii::app()->user->can($id, 'stop'))
                {
                    if (!McBridge::get()->serverCmd($id, 'stop'))
                        echo McBridge::get()->lastError();
                }
                break;
            case 'restart':
                if (Yii::app()->user->can($id, 'restart'))
                {
                    if (!McBridge::get()->serverCmd($id, 'restart'))
                        echo McBridge::get()->lastError();
                }
                break;
            case 'kick':
                if (Yii::app()->user->can($id, 'kick'))
                {
                    $player = @$_POST['player'];
                    if ($player)
                    {
                        if (!McBridge::get()->serverCmd($id, 'mc:kick '.preg_replace("/\n/", '', $player)))
                            echo McBridge::get()->lastError();
                    }
                }
                break;
            }
            Yii::app()->end();
        }

        $edit = Yii::app()->user->can($id, 'edit');

        $jars = false;
        if ($edit && ($settings->user_jar || Yii::app()->user->isSuperuser()))
            $jars = $this->listJars($id);

        $adv = false;
        if (@$_POST['run_chunkster'] === 'true' && (Yii::app()->user->isSuperuser()
            || ($edit && Yii::app()->params['user_chunkster'])))
        {
            McBridge::get()->serverCmd($id, 'start chunkster.jar');
            
            Yii::app()->user->setFlash('server', Yii::t('mc', 'Command sent, please check the server console for details'));
            $this->redirect(array('view','id'=>$model->id));
        }
        if(isset($_POST['Server']) && $edit)
        {
            $name = isset($_POST['Server']['name']) ? $_POST['Server']['name'] : false;
            $players = isset($_POST['Server']['players']) ? $_POST['Server']['players'] : false;
            if (Yii::app()->user->isSuperuser())
            {
                $model->scenario = 'superuser';
                $settings->scenario = 'superuser';
                $model->sendData = @$_POST['send_data'];
                $user = User::model()->findByPk((int)$_POST['user-assign']);
                $model->setOwner($user);
                if ($user)
                    $user->setServerFtpAccess($model->id, 'rw');
            }
            else
            {
                if (!$settings->user_name)
                    $name = $model->name;
                if (!$settings->user_players)
                    $players = $model->players;
                if ($settings->user_schedule)
                    $model->autosave = $_POST['Server']['autosave'];
                if ($settings->user_visibility)
                {
                    $model->default_level = $_POST['Server']['default_level'];
                    $settings->visible = $_POST['ServerConfig']['visible'];
                }
            }
            $model->attributes=$_POST['Server'];
            if ($name !== false)
                $model->name = $name;
            if ($players !== false)
                $model->players = $players;
            if (Yii::app()->user->isSuperuser() || ($settings->user_jar && in_array($model->jardir, array('server', 'server_base'))))
            {
                if (isset($_POST['Server']['jarfile']))
                    $model->jarfile = $_POST['Server']['jarfile'];
            }
            else if ($jars && $settings->user_jar && isset($_POST['jar-select']))
            {
                $jar = $_POST['jar-select'];
                //Accept the change if the selected value is valid and either a new value was selected
                //or the default was selected and the previous value was valid. This prevents overwriting
                //a custom (i.e. invalid) JAR entered by the admin when the user doesn't change the JAR from "Default"
                if (in_array($jar, array_keys($jars)) && ($jar || in_array($model->jarfile, array_keys($jars))))
                    $model->jarfile = $jar;
            }
            $settings->attributes=$_POST['ServerConfig'];
            $settings->give_role = @$_POST['cheat_role'];
            $settings->tp_role = @$_POST['cheat_role'];
            if($model->validate() && $settings->validate())
            {
                $model->save(false);
                $settings->save(false);
                Yii::log('Updated server '.$model->id);
                Yii::app()->user->setFlash('server', Yii::t('mc', 'Server settings saved.'));
                $moveTo = (int)@$_POST['move_files'];
                if ($moveTo)
                    McBridge::get()->cmd($model->daemon_id, 'server '.$model->id.':migrate '.$moveTo);
                $this->redirect(array('view','id'=>$model->id));
            }
            else
                $adv = true;
        }

        $my = array();
        if (!Yii::app()->user->isGuest && !Yii::app()->user->isSuperuser())
        {
            $cmd = Yii::app()->db->createCommand($this->ownServerSql);
            $cmd->bindValue(1, (int)Yii::app()->user->id);
            $ids = $cmd->queryColumn();
            $myModels = array();
            if (count($ids) > 1 && in_array($model->id, $ids))
                $myModels = Server::model()->findAllByAttributes(array('id'=>array_values($ids),), array('order'=>'lower(name) asc'));
            foreach ($myModels as $myModel)
                $my[$myModel->id] = substr($myModel->name, 0, 40).' ('.User::getRoleLabel(Yii::app()->user->model->getServerRole($myModel->id)).')';
        }

        $editConfigs = Yii::app()->user->can($id, 'edit configs');
        $plugins = false;
        if ($editConfigs && McBridge::get()->serverCmd($id, 'plugin has', $data))
        {
            if (@$data[0]['has'])
                $plugins = true;
        }

        $this->render('view',array(
            'model'=>$model,
            'settings'=>$settings,
            'chat'=>Yii::app()->user->can($id, 'chat'),
            'viewLog'=>Yii::app()->user->can($id, 'get log'),
            'getPlayers'=>Yii::app()->user->can($id, 'get players'),
            'editConfigs'=>$editConfigs,
            'manageUsers'=>Yii::app()->user->can($id, 'manage players'),
            'edit'=>$edit,
            'delete'=>Yii::app()->user->isSuperuser(),
            'buttons'=>Yii::app()->user->can($id, 'start'),
            'backup'=>Yii::app()->user->can($id, 'get backup'),
            'command'=>Yii::app()->user->can($id, 'command'),
            'jars'=>$jars,
            'data'=>$this->ajaxRefresh($model, $all),
            'advanced'=>$adv,
            'my'=>$my,
            'plugins'=>$plugins,
        ));
    }

    public function actionCreate()
    {
        $model = new Server('superuser');
        $settings = new ServerConfig('superuser');
        $this->performAjaxValidation($model);
        $adv = false;
        if(isset($_POST['Server']))
        {
            $model->attributes=$_POST['Server'];
            $settings->attributes=$_POST['ServerConfig'];
            $settings->give_role = @$_POST['cheat_role'];
            $settings->tp_role = @$_POST['cheat_role'];
            $model->sendData = @$_POST['send_data'];
            if($model->validate() && $settings->validate())
            {
                $model->save(false);
                $settings->server_id = $model->id;
                $settings->scenario = 'create';
                $settings->save();
                Yii::log('Created server '.$model->id);
                $user = User::model()->findByPk((int)$_POST['user-assign']);
                $model->setOwner($user);
                if ($user)
                    $user->setServerFtpAccess($model->id, 'rw');
                $model->createDefaultCommands();
                McBridge::get()->serverCmd($model->id, 'run_s:builtin:script setup');
                $this->redirect(array('view','id'=>$model->id));
            }
            else
                $adv = true;
        }
        $this->render('view',array(
            'model'=>$model,
            'settings'=>$settings,
            'edit'=>true,
            'jars'=>$this->listJars(),
            'advanced'=>$adv,
            'my'=>false,
        ));
    }

    public function actionUsers($id)
    {
        Yii::app()->user->can($id, 'manage players', true);
        $myRole = Yii::app()->user->serverRole($id);
        $settings = ServerConfig::model()->findByPk((int)$id);
        if (!$settings || !$settings->user_ftp)
            $userFtp = false;
        else
            $userFtp = true;
        $userFtp = Yii::app()->user->isSuperuser() || ($myRole == 'owner' && $userFtp);
        if (isset($_POST['ajax']))
        {
            if ($_POST['ajax'] == 'role')
            {
                $role = @$_POST['role'];
                $user = (int)@$_POST['user'];
                if ($role && ($role == $myRole || !in_array($role, User::$roles)
                        || $role == User::$roles[count(User::$roles)-1]))
                    die(Yii::t('mc', 'Invalid role selected.'));
                $user = User::model()->findByPk($user);
                if (!$user)
                    die(Yii::t('mc', 'Invalid user selected.'));
                if (User::getRoleLevel($myRole) <= $user->getLevel($id))
                    die(Yii::t('mc', 'No permission to change that user.'));
                Yii::log('Setting user role of user '.$user->id.' for server '.$id.' to '.$role);
                if (!$user->setServerRole($id, $role))
                    die(Yii::t('mc', 'Failed to change user role!'));
            }
            else if ($_POST['ajax'] == 'ftpAccess' && $userFtp)
            {
                $access = $_POST['ftpAccess'];
                $user = (int)$_POST['user'];
                $user = User::model()->findByPk($user);
                if (!$user)
                    die(Yii::t('mc', 'Invalid user selected.'));
                Yii::log('Setting FTP access of user '.$user->id.' for server '.$id.' to '.$access);
                if (!$user->setServerFtpAccess($id, $access))
                    die(Yii::t('mc', 'Failed to change user role!'));
            }
            Yii::app()->end();
        }
        $model=$this->loadModel($id);
        Yii::app()->params['view_server_id'] = $id;
        Yii::app()->params['view_role'] = $myRole;
        $users = new User('search');
        $users->unsetAttributes();  // clear any default values
        if(isset($_GET['User']))
            $users->attributes=$_GET['User'];

        $provider = $users->search();
        //hide list enabled and not superuser
        if ((Yii::app()->params['hide_userlist'] === true) && !Yii::app()->user->isSuperuser()
            && (strlen($users->name) < 2 || $users->name[0] != '=')) //not requesting exact match
        {
            $cond = "((select role from user_server where server_id=".$id." and user_id=id) != '' or name=:name)";
            $provider->criteria->params[':name'] = $users->name;
            $provider->criteria->addCondition($cond);
        }
        $provider->criteria->order = 'name asc';

        $this->render('users',array(
            'model'=>$model,
            'provider'=>$provider,
            'users'=>$users,
            'userFtp'=>$userFtp,
        ));
    }

    private function replData($str, $file, $dir)
    {
        $str = preg_replace('/{file}/', $file, $str);
        $str = preg_replace('/{dir}/', $dir, $str);
        return $str;
    }

    public function actionConfigs($id)
    {
        Yii::app()->user->can($id, 'edit configs', true);
        $configs = ConfigFile::model()->findAll();
        $dmn = Server::getDaemon($id);

        $list = array();
        $error = '';
        foreach ($configs as $cfg)
        {
            if (!$cfg->enabled)
                continue;
            $res = McBridge::get()->cmd($dmn, 'server '.$id.':cfgfile check:'.$cfg->file.':'.$cfg->dir.':');
            if (!$res['success'] || !isset($res['data'][0]))
            {
                $error = CHtml::encode($res['error']);
                continue;
            }
            foreach ($res['data'] as $data)
            {
                if ($data['valid'] != 'True')
                    continue;

                $name = $this->replData($cfg->name, $data['file'], $data['dir']);
                $desc = $this->replData($cfg->description, $data['file'], $data['dir']);
                $list[] = array('id'=>$cfg->id, 'name'=>$name, 'desc'=>$desc, 'ro'=>$data['ro'],
                    'file'=>$data['file'], 'dir'=>$data['dir'],
                    'action'=>$data['ro'] == 'True' ? 'View' : 'Edit');
            }                
        }

        $perm = McBridge::get()->cmd($dmn, 'server '.$id.':cfgfile check:[Pp]ermissions.[Jj][Aa][Rr]:plugins/:');
        if ($perm['success'] && isset($perm['data'][0]))
            $perm = true;
        else
            $perm = false;

    
        $this->render('configs',array(
            'dataProvider'=> new CArrayDataProvider($list, array(
                'sort'=>array(
                    'attributes'=>array(
                        'name', 
                    ),
                ),
                'pagination'=>array('pageSize'=>10),
            )),
            'model'=>$this->loadModel($id),
            'error'=>$error,
            'perm'=>$perm,
        ));
    }

    public function actionEditConfig($id, $config, $ro, $file, $dir)
    {
        Yii::app()->user->can($id, 'edit configs', true);
        $cfg = ConfigFile::model()->findByPk($config);

        if (!$cfg || !preg_match('/'.$cfg->file.'/', $file))
            throw new CHttpException(404, Yii::t('mc', 'Config file not found'));

        $rules = CJSON::decode($cfg->options);

        $ro = ($ro == Yii::t('mc', 'True') ? true : false);

        $error = false;
        $name = $this->replData($cfg->name, $file, $dir);

        if (!$ro && @$_POST['save'] === 'true')
        {
            $data = '';
            if ($cfg->type == 'properties')
            {
                $acceptAll = @$rules['*']['visible'] ? true : false;

                if (@count($_POST['option']))
                foreach ($_POST['option'] as $opt=>$val)
                {
                    if ($acceptAll || !isset($rules[$opt]['visible']) || $rules[$opt]['visible'])
                        $data .= $opt.'='.$val."\n";
                }
                
            }
            else
                $data = $_POST['list'];
            $data = $data ? str_replace(array('\\', ' ;'), array('\\\\', ' \;'), $data) : '';
            $data = preg_replace('/\n\r?/', ' ;', $data);
            $d = null;
            if (!McBridge::get()->serverCmd($id, 'cfgfile set'.$cfg->type.':'.$file.':'.$dir.':'.$data, $d))
                $error = McBridge::get()->lastError();
            else if (@$d[0]['accepted'] != 'True')
            {
                $error = isset($d[0]['message']) ? $d[0]['message'] : Yii::t('mc', 'Error updating config file!');
            }
            else
            {
                Yii::app()->user->setFlash('server', Yii::t('mc', 'Config File saved.'));
                $this->redirect(array('configs','id'=>$id));
            }
        }

        $data = null;
        if (!McBridge::get()->serverCmd($id, 'cfgfile get'.$cfg->type.':'.$file.':'.$dir.':', $data))
            $error = McBridge::get()->lastError();

        $opts = array();
        $list = '';

        if ($data && $cfg->type == 'properties')
        {
            if (@is_array($rules))
            foreach ($rules as $match=>$info)
            {
                if (isset($info['select']) && $info['select'] === 'bool')
                    $info['select'] = array('true'=>Yii::t('mc', 'Enabled'), 'false'=>Yii::t('mc', 'Disabled'));
                $found = false;
                foreach ($data as $k=>$v)
                {
                    $opt = array();
                    $opt['name'] = $v['option'];
                    $opt['value'] = $v['value'];
                    $opt['visible'] = true;
                    
                    if (strlen($opt['name']) && $match != '*' && $match != $opt['name'])
                        continue;

                    $found = true;
                    if (isset($info['visible']))
                        $opt['visible'] = $info['visible'];
                    if (isset($info['name']))
                        $opt['name'] = $info['name'];
                    if (isset($info['select']))
                        $opt['select'] = $info['select'];
                    if (isset($info['default']) && !strlen($opt['value']))
                        $opt['value'] = $info['default'];

                    if ($opt['visible'])
                        $opts[$v['option']] = $opt;

                    if ($match != '*')
                    {
                        unset($data[$k]);
                        break;
                    }
                }
                if (!$found && $match != '*' && !@$info['nocreate'] && (!isset($info['visible']) || $info['visible']))
                {
                    $opts[$match] = $info;
                    if (!@$opts[$match]['name'])
                        $opts[$match]['name'] = $match;
                    $opts[$match]['value'] = @$info['default'];
                }
            }
        }
        else if ($data)
        {
            foreach ($data as $line)
                if (isset($line['line']))
                    $list .= $line['line']."\n";            
        }

        $this->render('editConfig',array(
            'dataProvider'=> new CArrayDataProvider($data, array(
                'sort'=>array(
                    'attributes'=>array(
                        'name', 
                    ),
                ),
                'pagination'=>array('pageSize'=>10),
            )),
            'type'=>$cfg->type,
            'list'=>$list,
            'options'=>$opts,
            'name'=>$name,
            'model'=>$this->loadModel($id),
            'error'=>$error,
            'ro'=>$ro,
        ));
    }

    private function getRolePerms($role, $default, $prefix, $suffix, $build, $perms)
    {
        $perms = array_filter(array_map('trim', explode(',', $perms)));
        return array(
            'default'=>$default,
            'info'=>array(
                'prefix'=>$prefix,
                'suffix'=>$suffix,
                'build'=>$build,
            ),
            'permissions'=>$perms,
        );
    }

    public function toYmlStr($a)
    {
        if (is_array($a))
            return (count($a) ? strval($a[0]) : '');
        return strval($a);
    }

    public function actionEditPermissionsConfig($id)
    {
        Yii::app()->user->can($id, 'edit configs', true);
        $model = $this->loadModel($id);
        $error = '';
        $world = $model->world ? $model->world : 'world';
        if (@$_POST['save'] === 'true')
        {
            require_once(dirname(__FILE__).'/../extensions/spyc/spyc.php');
            $groups['groups'] = array();
            $def = User::getLevelRole($model->default_level);
            $prev = false;
            foreach (User::$roles as $role)
            {
                if ($role == 'none')
                    continue;
                $lbl = $role;//User::getRoleLabel($role);
                $groups['groups'][$lbl] = $this->getRolePerms($role, $role == $def,$_POST['prefix_'.$role],
                    $_POST['suffix_'.$role], $_POST['build_'.$role] == 0, $_POST['perms_'.$role]);
                if ($prev)
                    $groups['groups'][$lbl]['inheritance'] = array($prev);
                $prev = $lbl;
            }
            $plrs = Player::model()->findAllByAttributes(array('server_id'=>$model->id));
            $users['users'] = array();
            foreach ($plrs as $plr)
            {
                $users['users'][$plr->name] = array('groups'=>array(User::getLevelRole($plr->level)));
            }
            $groups = Spyc::YAMLDump($groups, 4, 0);
            $users = Spyc::YAMLDump($users, 4, 0);

            $groupsData = $groups ? str_replace(array('\\', ' ;'), array('\\\\', ' \;'), $groups) : '';
            $groupsData = preg_replace('/\n\r?/', ' ;', $groupsData);
            $usersData = $users ? str_replace(array('\\', ' ;'), array('\\\\', ' \;'), $users) : '';
            $usersData = preg_replace('/\n\r?/', ' ;', $usersData);
            $d = null;
            if (!McBridge::get()->serverCmd($id, 'cfgfile setlist:groups.yml:plugins/Permissions/'.$world.':'.$groupsData, $d))
                $error = McBridge::get()->lastError();
            else if (@$d[0]['accepted'] != 'True')
            {
                $error = isset($d[0]['message']) ? $d[0]['message'] : Yii::t('mc', 'Error updating config file!');
            }
            else if (!McBridge::get()->serverCmd($id, 'cfgfile setlist:users.yml:plugins/Permissions/'.$world.':'.$usersData, $d))
                $error = McBridge::get()->lastError();
            else if (@$d[0]['accepted'] != 'True')
            {
                $error = isset($d[0]['message']) ? $d[0]['message'] : Yii::t('mc', 'Error updating config file!');
            }
            else
            {
                Yii::app()->user->setFlash('server', Yii::t('mc', 'Config File saved.'));
                $this->redirect(array('configs','id'=>$id));
            }
        }
        else
        {
            require_once(dirname(__FILE__).'/../extensions/spyc/spyc.php');
            $groupsData = array();
            $usersData = array();
            if (!McBridge::get()->serverCmd($id, 'cfgfile getlist:groups.yml:plugins/Permissions/'.$world.':', $groupsData))
                $error = McBridge::get()->lastError();
            if (!McBridge::get()->serverCmd($id, 'cfgfile getlist:users.yml:plugins/Permissions/'.$world.':', $usersData))
                $error = McBridge::get()->lastError();
            $users = '';
            $groups = '';
            if (count($groupsData))
            foreach ($groupsData as $line)
                if (isset($line['line']))
                    $groups .= $line['line']."\n";
            if (count($usersData))
            foreach ($usersData as $line)
                if (isset($line['line']))
                    $users .= $line['line']."\n";
            $groups = Spyc::YAMLLoadString($groups);
            foreach (User::$roles as $role)
            {
                if ($role == 'none')
                    continue;
                $lbl = $role;//User::getRoleLabel($role);
                $_POST['prefix_'.$role] = @$groups['groups'][$lbl]['info']['prefix'];
                $_POST['suffix_'.$role] = @$groups['groups'][$lbl]['info']['suffix'];
                $_POST['build_'.$role] = isset($groups['groups'][$lbl]['info']['build']) && !$groups['groups'][$lbl]['info']['build'] ? 1 : 0;
                if (isset($groups['groups'][$lbl]['permissions'][0]) && $groups['groups'][$lbl]['permissions'][0])
                    $_POST['perms_'.$role] = implode(', ', array_map(array($this, 'toYmlStr'), $groups['groups'][$lbl]['permissions']));
            }
        }

        $this->render('editPermissionsConfig',array(
            'model'=>$model,
            'error'=>$error,
        ));
    }

    private function esc($str)
    {
        return str_replace(':', ';', $str);
    }
    public function actionBgPlugins($id, $installed = false)
    {
        if (Yii::app()->params['use_bukget'] !== true)
            throw new CHttpException(404, Yii::t('mc', 'The requested page does not exist.'));
        Yii::app()->user->can($id, 'edit configs', true);

        $plugins = new BgPlugin('search');
        $plugins->unsetAttributes();
        if(isset($_GET['BgPlugin']))
            $plugins->attributes=$_GET['BgPlugin'];

        $cats = $plugins->allCategories;
        $plugins->checkPlugins();

        $plugins->categories = isset($_POST['cat']) ? $_POST['cat'] : @$_GET['cat'];
        if (is_array($plugins->categories))
            $plugins->categories = '';

        $this->render('bgPlugins',array(
            'model'=>$this->loadModel($id),
            'cats'=>$cats,
            'plugins'=>$plugins,
            'installed'=>$installed,
        ));
    }

    public function actionBgPlugin($id, $name, $installed = false, $action = '')
    {
        if (Yii::app()->params['use_bukget'] !== true)
            throw new CHttpException(404, Yii::t('mc', 'The requested page does not exist.'));
        Yii::app()->user->can($id, 'edit configs', true);
        $model = $this->loadModel($id);
        $plugin = BgPlugin::model()->findByAttributes(array('name'=>$name));
        $info = BgPluginInfo::model()->findByPk(array('server_id'=>$model->id, 'name'=>$plugin->name));
        if ($info)
            $info->plugin = $plugin;

        if (isset($_POST['ajax']))
        {
            switch ($_POST['ajax'])
            {
            case 'get_status':
                $i = $info ? $info->installed : '';
                if ($info && $info->disabled)
                    $i = 'disabled';
                echo CJSON::encode($i);
                break;
            }
            Yii::app()->end();
        }
        if (isset($_POST['action']))
        {
            $action = $_POST['action'];
            switch ($action)
            {
            case 'install':
            case 'update':
                if (!McBridge::get()->serverCmd($id, 'bgplugin install:'.$this->esc($name)
                    .':'.$this->esc($plugin->version).':'.$plugin->downloadLink))
                {
                    Yii::app()->user->setFlash('server',
                        Yii::t('mc', 'Failed to {action} plugin', array('{action}'=>$action)).': '.CHtml::encode(McBridge::get()->lastError()));
                }
                break;
            case 'remove':
            case 'enable':
            case 'disable':
                if (!McBridge::get()->serverCmd($id, 'bgplugin '.$action.':'.$this->esc($name)))
                {
                    Yii::app()->user->setFlash('server',
                        Yii::t('mc', 'Failed to {action} plugin', array('{action}'=>$action)).': '.CHtml::encode(McBridge::get()->lastError()));
                }
                break;
            }
            $this->redirect(array('bgPlugin', 'id'=>$id, 'name'=>$name, 'action'=>$action));
        }

        $this->render('bgPlugin',array(
            'model'=>$model,
            'plugin'=>$plugin,
            'info'=>$info,
            'installed'=>$installed,
            'action'=>$action,
        ));
    }

    public function actionPlugins($id)
    {
        Yii::app()->user->can($id, 'edit configs', true);

        if (isset($_GET['action']))
        {
            switch ($_GET['action'])
            {
            case 'unpack':
            case 'install':
                if (!McBridge::get()->serverCmd($id, 'plugin add:'.$_GET['file']))
                {
                    Yii::app()->user->setFlash('server',
                        Yii::t('mc', 'Failed to install plugin').': '.CHtml::encode(McBridge::get()->lastError()));
                }
                if ($_GET['action'] == 'unpack')
                {
                    Yii::app()->user->setFlash('plugin_unpack',
                        Yii::t('mc', 'The plugin archive is being unpacked, please see the server log.'));
                }
                break;
            case 'remove':
                if (!McBridge::get()->serverCmd($id, 'plugin remove:'.$_GET['file']))
                {
                    Yii::app()->user->setFlash('server',
                        Yii::t('mc', 'Failed to remove plugin').': '.CHtml::encode(McBridge::get()->lastError()));
                }
                break;
            }
            $this->redirect(array('plugins', 'id'=>$id, 'sort'=>@$_GET['sort']));
        }

        $filter = new PluginFilter;
        $filter->unsetAttributes();
        if(isset($_GET['PluginFilter']))
            $filter->attributes = $_GET['PluginFilter'];
        $filter->prepareFilters();

        $list = array();
        $error = '';
        $data = array();
        $haveItems = false;
        if (!McBridge::get()->serverCmd($id, 'plugin list', $data))
            Yii::app()->user->setFlash('server', CHtml::encode(McBridge::get()->lastError()));
        else
        {
            $haveItems = count($data);
            foreach ($data as $d)
            {
                $st = array(
                    'installed'=>array(Yii::t('mc', 'Installed'), array('remove'=>Yii::t('mc', 'Remove'))),
                    'outdated'=>array(Yii::t('mc', 'Outdated'), array('install'=>Yii::t('mc', 'Update'), 'remove'=>Yii::t('mc', 'Remove'))),
                    'none'=>array(Yii::t('mc', 'Not Installed'), array('install'=>Yii::t('mc', 'Install'))),
                );
                $l = array('id'=>1, 'file'=>strtolower($d['file']), 'desc'=>$d['desc'],
                    'status'=>$d['status']);
                if (!$filter->filter($l))
                    continue;
                $actions = '';
                foreach ($st[$d['status']][1] as $action => $label)
                {
                    if (preg_match('/\.zip$/i', $d['file']))
                    {
                        $label = Yii::t('mc', 'Unpack');
                        $action = 'unpack';
                        $l['status'] = '';
                    }
                    $actions .= CHtml::link($label, array('plugins', 'id'=>$id, 'file'=>$d['file'],
                        'action'=>$action, 'sort'=>@$_GET['sort'])).' ';
                }
                if ($l['status'])
                    $l['status'] = '_'.$l['status'];
                $l['status_alt'] = @$st[$d['status']][0];
                $l['action'] = $actions;
                $l['displayFile'] = $d['file'];
                $list[] = $l;
            }                
        }

    
        $this->render('plugins',array(
            'dataProvider'=> new CArrayDataProvider($list, array(
                'sort'=>array(
                    'attributes'=>array(
                        'file',
                        'status',
                    ),
                    'defaultOrder'=>array('file'=>CSort::SORT_ASC),
                ),
                'pagination'=>array('pageSize'=>10),
            )),
            'filter'=>$filter,
            'haveItems'=>$haveItems,
            'model'=>$this->loadModel($id),
        ));
    }

    public function actionDelete($id)
    {
        $model = $this->loadModel($id);

        $canDel = false;
        $shared = Server::model()->findAllByAttributes(array('daemon_id'=>$model->daemon_id, 'dir'=>$model->dir));
        $cmd = Yii::app()->db->createCommand('select `server_id` from `user_server` where `role`=\'owner\' and `user_id`=?');
        $cmd->execute(array($model->owner));
        $us = $cmd->queryColumn();
        $owned = Server::model()->findAllByAttributes(array('id'=>$us));

        if(@$_POST['delete'] === 'true')
        {
            if (isset($_POST['del_files']))
            {
                if (!in_array($_POST['del_files'], array(Yii::t('mc', 'yes'), Yii::t('mc', 'no'))))
                {
                    Yii::app()->user->setFlash('server', Yii::t('mc', 'Please enter "{yes}" or "{no}"',
                        array('{yes}'=>Yii::t('mc', 'yes'), '{no}'=>Yii::t('mc', 'no')))
                    );
                    $this->redirect(array('delete', 'id'=>$model->id));
                }
                if (strtolower($_POST['del_files']) === Yii::t('mc', 'yes'))
                    $model->deleteDir = 'yes';
            }

            $owner = $model->owner;
            //delete server, then delete user
            Yii::log('Deleting server '.$model->id.' (delete dir: '.$model->deleteDir.')');
            if ($model->delete() && @$_POST['del_user'])
                User::model()->findByPk($owner)->delete();            

            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
        }

        if (!McBridge::get()->serverCmd($model->id, 'get status', $status))
            $status = 'error';
        else
            $status = $status[0]['status'];
        if ($status == 'stopped' && count($shared) <= 1)
            $canDel = true;
        $this->render('delete',array(
            'model'=>$model,
            'shared'=>$shared,
            'canDel'=>$canDel,
            'owned'=>$owned,
            'status'=>$status,
            'info'=>$model->dbInfo,
        ));
    }

    private function errStr($error)
    {
        return CHtml::encode($error);
    }

    private function ajaxRefresh($server, $type)
    {
        $all = ($type === 'all');
        if (!is_array($type))
            $type = array($type);
        $ret = array();

        $status = False;

        if ($all || in_array('players', $type))
        {
            $error = false;
            ob_start();
            if (!Yii::app()->user->can($server->id, 'get players'))
                $error = Yii::t('mc', 'Permission denied.');
            else if (!McBridge::get()->serverCmd($server->id, 'get players', $players))
                $error = McBridge::get()->lastError();
                
            $i = 0;
            if (is_array($players) && count($players)):
                $kick = Yii::app()->user->can($server->id, 'kick');
                foreach ($players as $player):
                    
            ?>
                    <tr class="<?php if (!($i % 2)) echo 'even'; else echo 'odd' ?>">
                        <td>
                            <?php
                                $nick = '<span'.($this->ip == @$player['ip']
                                    ? ' style="font-weight: bold; color: blue"' : '').'>'
                                        .CHtml::encode(@$player['name']).'</span>';
                                if (@$player['id'])
                                    echo CHtml::link($nick, array('player/view', 'id'=>$player['id']));
                                else
                                    echo $nick;

                            ?>
                            <?php if ($kick): ?>
                                <div style="float: right">
                                    <?php echo CHtml::link('Kick', 'javascript:kickPlayer(\''.addslashes(@$player['name']).'\')'); ?>
                                </div>
                            <?php endif ?>
                        </td>
                    </tr>
            <?php
                $i++;
                endforeach;
            else:
            ?>
                <tr>
                    <td class="odd" style="text-align: center">
                    <?php if (strlen($error))
                        echo Yii::t('mc', 'Error getting player list: ').$this->errStr($error);
                    else
                        echo Yii::t('mc', 'No players online');
                    ?>
                    </td>
                </tr>
            <?php
            endif;
            $ret['players'] = ob_get_clean();
        }
        if ($all || in_array('chat', $type))
        {
            $error = false;
            ob_start();
            if (!Yii::app()->user->can($server->id, 'get chat'))
                $error = Yii::t('mc', 'Permission denied.');
            else if (!McBridge::get()->serverCmd($server->id, 'get chat', $chat))
                $error = McBridge::get()->lastError();
            if (strlen($error))
                echo Yii::t('mc', 'Couldn\'t get chat: ').$this->errStr($error);
            else
            {
                for($i = count($chat) - 1; $i >= 0; $i--)
                    echo @strftime('%H:%M:%S', $chat[$i]['time']).' '.str_pad('<'.$chat[$i]['name']
                            .'>', 25).$chat[$i]['text']."\n";
                echo "\n";
            }
            $ret['chat'] = ob_get_clean();
        }
        if ($all || in_array('status', $type)
                 || in_array('statusdetail', $type)
                 || in_array('statusicon', $type))
        {
            $error = false;
            ob_start();
            if (!Yii::app()->user->can($server->id, 'get status'))
                $error = Yii::t('mc', 'Permission denied.');
            else if (!McBridge::get()->serverCmd($server->id, 'get status', $status))
                $ret['status'] = McBridge::get()->lastError();
                
            if (strlen($error))
                echo Yii::t('mc', 'Error getting server status: ').$this->errStr($error);
            else
            {
                $ret['statusdetail'] = Yii::t('mc', 'Offline');
                $st = @$status[0];
                switch (@$st['status'])
                {
                case 'running':
                    echo Yii::t('mc', 'The server is online!');
                    $ret['statusdetail'] = Yii::t('mc', 'Online').', '.@$st['players'].'/'.@$st['maxPlayers'].' '.Yii::t('mc', 'players');
                    $ret['statusicon'] = Theme::img('online.png');
                    break;
                case 'stopped':
                    echo Yii::t('mc', 'The server is offline.');
                    $ret['statusicon'] = Theme::img('offline.png');
                    break;
                default:
                    echo Yii::t('mc', 'The server status is currently changing.');
                    $ret['statusicon'] = Theme::img('changing.png');
                }
                if (@$st['pid'] && Yii::app()->user->isSuperuser())
                    $ret['statusdetail'] .= ' ('.Yii::t('mc', 'PID').': '.@$st['pid'].')';
            }
            $ret['status'] = ob_get_clean();
            
        }
        if ($all || in_array('resources', $type))
        {
            $error = false;
            ob_start();
            if (!Yii::app()->user->can($server->id, 'get log'))
                $error = Yii::t('mc', 'Permission denied.');
            else if (!McBridge::get()->serverCmd($server->id, 'get resources', $res))
                $error = McBridge::get()->lastError();
                
            if (!strlen($error))
            {
                $st = @$res[0];
                $this->renderPartial('resources', array('cpu'=>@$st['cpu'], 'memory'=>@$st['memory']));
            }
            $ret['resources'] = ob_get_clean();
        }
        if ($all || in_array('log', $type))
        {
            $error = false;
            ob_start();
            if (!Yii::app()->user->can($server->id, 'get log'))
                $error = Yii::t('mc', 'Permission denied.');
            else if (!McBridge::get()->serverCmd($server->id, 'get log', $log))
                $error = McBridge::get()->lastError();

            if (strlen($error))
                echo Yii::t('mc', 'Couldn\'t get log: ').$this->errStr($error);
            else
            {
                for($i = count($log) - 1; $i >= 0; $i--)
                    echo @$log[$i]['line']."\n";
            }
            $ret['log'] = ob_get_clean();
        }
        if ($all || in_array('buttons', $type))
        {
            $error = false;
            ob_start();
            if (!$status)
            {
                if (!Yii::app()->user->can($server->id, 'get status'))
                    $error = Yii::t('mc', 'Permission denied.');
                else if (!McBridge::get()->serverCmd($server->id, 'get status', $status))
                    $error = McBridge::get()->lastError();
            }
            
            $off = '1'; $on = '0';
            $b1 = $b2 = $b3 = $off;
            if (Yii::app()->user->can($server->id, 'start'))
                $b1 = $on;
            if (Yii::app()->user->can($server->id, 'stop'))
                $b2 = $on;
            if (Yii::app()->user->can($server->id, 'restart'))
                $b3 = $on;
            switch (@$status[0]['status'])
            {
            case 'running':
                $b1 = $off;
                break;
            case 'stopped':
                $b2 = $off;
                break;
            default:
                $b1 = $b3 = $off;
            }
            
            echo $b1.$b2.$b3;

            $ret['buttons'] = ob_get_clean();
        }
        if (/*$all ||*/ in_array('backup', $type))
        {
            $error = false;
            ob_start();
            if (!Yii::app()->user->can($server->id, 'get backup'))
                $error = Yii::t('mc', 'Permission denied.');
            else if (!McBridge::get()->serverCmd($server->id, 'backup status', $backup))
                $error = McBridge::get()->lastError();
                
            $dis = array('disabled'=>'disbled');
            $start = $download = false;
            $cls = 'flash-success';
            switch ($backup[0]['status'])
            {
            case 'none':
                $content = Yii::t('mc', 'No backup in progress');
                $start = true;
                break;
            case 'done':
                $content = Yii::t('mc', 'Backup done, ready for download. (Created: {date})', array('{date}'=>@date('Y-m-d H:i', $backup[0]['time'])));
                $start = $download = true;
                break;
            case 'running':
                $content = Yii::t('mc', 'Backup in progress, please wait');
                break;
            case 'error':
            default:
                if (!$error)
                    $error = $backup[0]['message'];
                $content = $error ? $error : Yii::t('mc', 'Error during backup, please check the daemon log');
                $cls = 'flash-error';
                $start = true;
                break;
            }

            echo '<div class="'.$cls.'">'.CHtml::encode($content).'</div>';         
   
            if (Yii::app()->user->can($server->id, 'start backup'))
                echo CHtml::ajaxButton(Yii::t('mc', 'Start'), '', array(
                        'type'=>'POST', 'data'=>array('ajax'=>'start',
                            Yii::app()->request->csrfTokenName=>Yii::app()->request->csrfToken,),
                        'success'=>'backup_response'
                    ),
                    $start ? array() : $dis);

            if (@is_readable($backup[0]['file']))
            {
                $opt = $download ? array() : $dis;
                $opt['onClick'] = 'backup_download()';  
                echo CHtml::button(Yii::t('mc', 'Download'), $opt);
            }
            else if (@$backup[0]['ftp'])
            {
                echo '<br/>';
                echo '<br/>';
                if (@Yii::app()->params['ftp_client_disabled'] !== true)
                {
                    echo Yii::t('mc', 'You can use the {link} to access your backup. For all other FTP clients, please use the information below.', array('{link}'=>CHtml::link('Multicraft FTP client', array('/ftpClient', 'id'=>$server->id))));
                    echo '<br/>';
                    echo '<br/>';
                }
                echo Yii::t('mc', 'The backup is available as "<b>{file}</b>" on the following FTP server:',
                    array('{file}'=>CHtml::encode(basename($backup[0]['file'])))).'<br/>';
                $ftp = explode(':', $backup[0]['ftp']);
                $ip = @$ftp[0];
                $port = @$ftp[1];
                $dmn = Daemon::model()->findByPk($server->daemon_id);
                if ($dmn && isset($dmn->ftp_ip) && isset($dmn->ftp_port))
                {
                    $ip = $dmn->ftp_ip;
                    $port = $dmn->ftp_port;
                }
                $attr = array();
                $attr[] = array('label'=>Yii::t('mc', 'Host'), 'value'=>$ip);
                $attr[] = array('label'=>Yii::t('mc', 'Port'), 'value'=>$port);
                $attr[] = array('label'=>Yii::t('mc', 'User'), 'value'=>Yii::app()->user->name.'.'.$server->id);
                $attr[] = array('label'=>Yii::t('mc', 'Password'), 'value'=>Yii::t('mc', 'Your Multicraft login password'));
                $this->widget('zii.widgets.CDetailView', array(
                    'data'=>array(),
                    'attributes'=>$attr,
                )); 
            }
            else if ($download)
            {
                echo Yii::t('mc', 'Your backup is available as "<b>{file}</b>" in your servers base directory.',
                    array('{file}'=>CHtml::encode(basename($backup[0]['file']))));
            }

            $ret['backup'] = ob_get_clean();
        }
        if ($all || in_array('movestatus', $type))
        {
            $error = false;
            ob_start();
            try
            {
                $sql = 'select `src_dmn`, `dst_dmn`, `status`, `message` from `move_status` where `server_id`=?';
                $cmd = Yii::app()->bridgeDb->createCommand($sql);
                $cmd->bindValue(1, $server->id);
                $row = $cmd->queryRow(false);
            }
            catch (Exception $e)
            {
                $row = array(0, 0, 'error', 'Failed to load status from database.');
            }
            if($row && Yii::app()->user->isSuperuser()):
                $flash = 'success';
                if ($row[2] == 'error')
                    $flash = 'error';

            $msg = array(
                'started'=>Yii::t('mc', 'Server move started'),
                'packing'=>Yii::t('mc', 'Packing server files on source daemon'),
                'uploading'=>Yii::t('mc', 'Uploading server files to new daemon'),
                'notifying'=>Yii::t('mc', 'Notifying new daemon of completed transfer'),
                'transferred'=>Yii::t('mc', 'Transfer complete, starting unpack'),
                'unpacking'=>Yii::t('mc', 'Unpacking server files on destination daemon'),
                'unsuspending'=>Yii::t('mc', 'Unsuspending server'),
                'done'=>Yii::t('mc', 'Servermove completed.'),
                'error'=>Yii::t('mc', 'Server move failed, please check the server console and multicraft.log. Last error:'),
            );

            $msg = @$msg[$row[2]];
            if (!$msg)
                $msg = $row[2];

            ?>
            <div class="flash-<?php echo $flash ?>">
                <span style="float: right">
                    <?php echo CHtml::link(Yii::t('mc', 'Dismiss'), array('dismiss', 'id'=>$server->id)) ?>
                </span>
                <?php echo Yii::t('mc', 'Server move status from daemon {a} to daemon {b}:', array('{a}'=>$row[0], '{b}'=>$row[1])) ?>
                <br/>
                <?php echo $msg ?><br/>
                <?php echo $row[3] ?>
            </div>
            <?php endif;
            $ret['movestatus'] = ob_get_clean();
        }
        return $ret;
    }

    public function actionChat($id)
    {
        Yii::app()->user->can($id, 'chat', true);
        $model = $this->loadModel($id);
        $all = array('players', 'chat', 'status');
        if (isset($_POST['ajax']))
        {
            switch($_POST['ajax'])
            {
            case 'refresh':
                echo CJSON::encode($this->ajaxRefresh($model, $all));
                break;
            case 'clearChat':
                if (!Yii::app()->user->can($id, 'clear chat'))
                    echo Yii::t('mc', 'Permission denied');
                else if (!McBridge::get()->serverCmd($model->id, 'clear chat'))
                    echo Yii::t('mc', 'Failed to clear chat: ').McBridge::get()->lastError();
                break;
            case 'chat':
                $message = $_POST['message'];
                if (!Yii::app()->user->can($id, 'chat'))
                    echo Yii::t('mc', 'Permission denied');
                else if (preg_match("/[\n\\\\\\/'\";&|$]/", $message))
                    echo Yii::t('mc', 'Please don\'t use special characters.');
                else if (strlen($message) > 512)
                    echo Yii::t('mc', 'Message too long.');
                else if (strlen($message))
                {
                    $from = Yii::app()->user->isGuest ? Yii::t('mc', 'Guest') : Yii::app()->user->name;
                    if (McBridge::get()->serverCmd($model->id, 'get players', $players))
                    {
                        foreach ($players as $player)
                            if ($player['ip'] == $this->ip)
                            {
                                $from .= ' ('.$player['name'].')';
                                break;
                            }
                    }
                    if (!McBridge::get()->serverCmd($model->id, 'mc:say <'.$from.'> '.$message, $tmp))
                        echo Yii::t('mc', 'Error sending chat: ').McBridge::get()->lastError();
                }
                break;
            case 'kick':
                if (Yii::app()->user->can($id, 'kick'))
                {
                    $player = @$_POST['player'];
                    if ($player)
                        McBridge::get()->serverCmd($id, 'mc:kick '.preg_replace("/\n/", '', $player));
                }
                break;
            }
            Yii::app()->end();
        }
        $this->render('chat',array(
            'model'=>$this->loadModel($id),
            'getChat'=>Yii::app()->user->can($id, 'get chat'),
            'chat'=>Yii::app()->user->can($id, 'chat'),
            'getPlayers'=>Yii::app()->user->can($id, 'get players'),
            'give'=>Yii::app()->user->can($id, 'give'),
            'tp'=>Yii::app()->user->can($id, 'tp'),
            'viewLog'=>Yii::app()->user->can($model->id, 'get log'),
            'command'=>Yii::app()->user->can($id, 'command'),
            'data'=>$this->ajaxRefresh($model, $all),
        ));
    }

    public function actionLog($id)
    {
        Yii::app()->user->can($id, 'get log', true);
        $model = $this->loadModel($id);
        $all = array('log', 'status');
        if (isset($_POST['ajax']))
        {
            switch($_POST['ajax'])
            {
            case 'refresh':
                echo CJSON::encode($this->ajaxRefresh($model, $all));
                break;
            case 'clearLog':
                if (!Yii::app()->user->can($id, 'clear log'))
                    echo Yii::t('mc', 'Permission denied');
                else if (!McBridge::get()->serverCmd($model->id, 'clear log'))
                    echo Yii::t('mc', 'Failed to clear log: ').McBridge::get()->lastError();
                break;
            case 'command':
                $cmd = $_POST['command'];
                if (!Yii::app()->user->can($id, 'command'))
                    echo Yii::t('mc', 'Permission denied');
                else if (preg_match("/[\n\\\\]/", $cmd))
                    echo Yii::t('mc', 'Please don\'t use special characters.');
                else if (strlen($cmd) > 512)
                    echo Yii::t('mc', 'Message too long.');
                else if (strlen($cmd))
                {
                    if (!McBridge::get()->serverCmd($model->id, (Yii::app()->user->isSuperuser() ? 'run_s:' : 'run:').$cmd))
                        echo Yii::t('mc', 'Error sending command: ').McBridge::get()->lastError();
                }
                break;
            }
            Yii::app()->end();
        }
        $this->render('log',array(
            'model'=>$this->loadModel($id),
            'data'=>$this->ajaxRefresh($model, $all),
            'command'=>Yii::app()->user->can($id, 'command'),
        ));
        
    }

    public function actionIndex($my = false)
    {
        $models = array();
        $order = array('order'=>'lower(name) asc');
        if (isset($_POST['ajax']))
        {
            switch($_POST['ajax'])
            {
            case 'get_status':
                $id = (int)@$_POST['server'];
                $sv = Server::model()->findByPk($id);
                $data = 0;
                $susp = 0;
                $maxpl = 0;
                $chat = Yii::app()->user->can($id, 'chat') ? 1 : 0;
                if ($sv)
                {
                    $data = $sv->getOnlinePlayers();
                    $susp = $sv->suspended;
                    $maxpl = $sv->players;
                }
                echo CJSON::encode(array('id'=>$id, 'sp'=>$susp, 'pl'=>$data, 'max'=>$maxpl, 'chat'=>$chat));
                break;
            }
            Yii::app()->end();
        }
        if (Yii::app()->user->isSuperuser())
        {
            $models = Server::model()->findAll($order);
        }
        else
        {
            $ids = array();
            if (!$my)
            {
            //get default visible
            $sql = 'select `id` from `server` where `default_level`>=?';
            $cmd = Yii::app()->bridgeDb->createCommand($sql);
            $cmd->bindValue(1, (int)User::getRoleLevel('guest'));
            $ids = $cmd->queryColumn();

            //remove never visible
            $sql = 'select `server_id` from `server_config` where `visible`!=1';
            $cmd = Yii::app()->db->createCommand($sql);
            $ids = array_diff($ids, $cmd->queryColumn());

            //add always visible
            /*$sql = 'select `server_id` from `server_config` where `visible`=';
            $cmd = Yii::app()->db->createCommand($sql);
            $ids = array_merge($ids, $cmd->queryColumn());*/
            }

            if (!Yii::app()->user->isGuest)
            {
                //add user visible
                $cmd = Yii::app()->db->createCommand($this->ownServerSql);
                $cmd->bindValue(1, (int)Yii::app()->user->id);
                $ids = array_merge($ids, $cmd->queryColumn());
            }
            if ($my && count($ids) == 1)
                $this->redirect(array('server/view', 'id'=>$ids[0]));
            if (count($ids))
                $models = Server::model()->findAllByAttributes(array('id'=>array_values($ids),), $order);
        }

        if ($spp = Setting::model()->findByPk('serversPerPage'))
            $spp = max(1, (int)$spp->value);

        $this->render('index',array(
            'dataProvider'=> new CArrayDataProvider($models, array(
                'sort'=>array(
                    'attributes'=>array(
                        'name', 
                    ),
                ),
                'pagination'=>array('pageSize'=>$spp),
            )),
            'my'=>$my,
        ));
    }

    public function actionAdmin()
    {
        $model = new Server('search');
        $model->unsetAttributes();
        if (isset($_GET['Server']))
            $model->attributes = $_GET['Server'];

        $this->render('admin', array(
            'model'=>$model,
        ));
    }

    public function actionBackup($id)
    {
        Yii::app()->user->can($id, 'get backup', true);
        $model = $this->loadModel($id);
        if (isset($_POST['ajax']))
        {
            switch($_POST['ajax'])
            {
            case 'refresh':
                echo CJSON::encode($this->ajaxRefresh($model, 'backup'));
                break;
            case 'start':
                if (!Yii::app()->user->can($id, 'start backup'))
                    die(Yii::t('mc', 'Access denied!'));
                if (!McBridge::get()->serverCmd($id, 'backup start', $none))
                    echo McBridge::get()->lastError();
                break;
            case 'download':
                if (!McBridge::get()->serverCmd($id, 'backup status', $status))
                    die(McBridge::get()->lastError());

                $file = $status[0]['file'];
                if (!$file)
                    die(Yii::t('mc', 'Failed to open file!'));
                $size = (string)(@filesize($file));
                
                if (!$size)
                    throw new CHttpException(404,Yii::t('mc', 'File not found!'));

                header('Content-Disposition:attachment; filename="'.$file.'"');
                header('Content-type: binary/octet-stream');
                header('Content-transfer-encoding: binary');
                header('Content-length: '.$size);
                readfile($file);
                break;
            }
            Yii::app()->end();
        }
                
        
        $this->render('backup',array(
            'model'=>$this->loadModel($id),
            'data'=>$this->ajaxRefresh($model, 'backup'),
        ));
    }

    public function actionFtp($id)
    {
        Yii::app()->user->can($id, 'manage players', true);
        $model = $this->loadModel($id);

        $dmnInfo = array('ip'=>'', 'port'=>'');
        if (!Yii::app()->user->model || !Yii::app()->user->model->getServerFtpAccess($model->id))
            $dmnInfo['error'] = Yii::t('mc', 'You don\'t have an FTP account for this server.');
        else
        {
            $dmn = Daemon::model()->findByPk($model->daemon_id);
            if (!$dmn)
                $dmnInfo['error'] = Yii::t('mc', 'No daemon found for this server.');
            else if (isset($dmn->ftp_ip) && isset($dmn->ftp_port))
                $dmnInfo = array('ip'=>$dmn->ftp_ip, 'port'=>$dmn->ftp_port);
            else
                $dmnInfo['error'] = Yii::t('mc', 'Daemon database not up to date, please run the Multicraft installer.');
        }

        $this->render('ftp',array(
            'model'=>$model,
            'dmnInfo'=>$dmnInfo,
        ));
    }

    public function actionRestore($id, $restore = false, $file = false)
    {
        Yii::app()->user->can($id, 'start backup', true);
        $model = $this->loadModel($id);

        if ($restore)
        {
            if (!preg_match('/^[^\/\\\?%\*:"\'<>\0]+\.zip/i', $file))
                Yii::app()->user->setFlash('server', Yii::t('mc', 'Filename contains invalid characters'));
            else if (!McBridge::get()->serverCmd($id, 'backup restore '.$file, $none))
            {
                $err = McBridge::get()->lastError();
                if ($err == 'inprogress')
                    Yii::app()->user->setFlash('server', Yii::t('mc', 'Failed to unpack archive because another unpack operation is currently in progress'));
                else if ($err == 'invalid')
                    Yii::app()->user->setFlash('server', Yii::t('mc', 'Failed to unpack archive because it contains invalid files (absolute paths or paths outside of the unpack directory). See the log for details.'));
                else if ($err == 'forbidden')
                    Yii::app()->user->setFlash('server', Yii::t('mc', 'Failed to unpack archive because it contains restricted file types. See the log for details.'));
                else
                    Yii::app()->user->setFlash('server', CHtml::encode($err));
            }
            $this->redirect(array('restore', 'id'=>$id));
            Yii::app()->end();
        }

        $list = array();
        $error = '';
        $data = array();
        if (!McBridge::get()->serverCmd($id, 'backup list', $data))
            Yii::app()->user->setFlash('server', CHtml::encode(McBridge::get()->lastError()));
        else
        {
            $files = array();
            foreach ($data as $d)
                $files[$d['file']] = $d['time'];
            ksort($files);
            foreach ($files as $f=>$t)
                $list[] = array('id'=>$f, 'time'=>@date("d.m.Y H:i", $t));            
        }

    
        $this->render('restore',array(
            'dataProvider'=> new CArrayDataProvider($list, array(
                'pagination'=>array('pageSize'=>10),
            )),
            'model'=>$this->loadModel($id),
        ));
    }

    public function actionMysqlDb($id, $cmd = '')
    {
        Yii::app()->user->can($id, 'edit configs', true);
        $model = $this->loadModel($id);
        $settings = ServerConfig::model()->findByPk((int)$id);
        if (!strlen($model->mysqlHost)
            || !((Yii::app()->params['user_mysql'] && $settings->user_mysql) || Yii::app()->user->isSuperuser()))
            Yii::app()->user->deny();
        if ($cmd == 'create')
        {
            if (!$model->createDatabase())
            {
                Yii::app()->user->setFlash('server_error',
                    Yii::t('mc', 'Failed to create MySQL database "{db}"!', array('{db}'=>
                        CHtml::encode($model->mysqlPrefix.$model->id))));
            }
            $this->redirect(array('mysqlDb', 'id'=>$id));
        }
        else if ($cmd == 'passwd')
        {
            if (!$model->changeDatabasePw())
            {
                Yii::app()->user->setFlash('server_error',
                    Yii::t('mc', 'Failed to change MySQL password for "{db}"!', array('{db}'=>
                        CHtml::encode($model->mysqlPrefix.$model->id))));
            }
            $this->redirect(array('mysqlDb', 'id'=>$id));
        }
        else if ($cmd == 'delete')
        {
            if (!$model->deleteDatabase())
            {
                Yii::app()->user->setFlash('server_error',
                    Yii::t('mc', 'Failed to delete MySQL database "{db}"!', array('{db}'=>
                        CHtml::encode($model->mysqlPrefix.$model->id))));
            }
            $this->redirect(array('mysqlDb', 'id'=>$id));
        }
        $this->render('mysqlDb',array(
            'model'=>$model,
            'info'=>$model->dbInfo,
        ));
    }

    public function loadModel($id)
    {
        $model=Server::model()->findByPk((int)$id);
        if($model===null)
            throw new CHttpException(404,Yii::t('mc', 'The requested page does not exist.'));
        return $model;
    }

    protected function performAjaxValidation($model)
    {
        if(isset($_POST['ajax']) && $_POST['ajax']==='server-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}

class DummyDb
{
    public function createCommand()
    {
        return $this;
    }

    public function bindValue() {}

    public function quoteColumnName() {}

    public function quoteValue() {}

    public function execute() {}
}

