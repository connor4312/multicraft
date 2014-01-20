<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class Server extends ActiveRecord
{
/*
    int $id
    string $name
    string $ip
    int $port
    string $dir
    string $world
    int $players
    int $memory
    int $start_memory
    string $jarfile
    string $autostart
    string $default_level
    int $daemon_id
    int $announce_save
    int $kick_delay
    int $suspended
    int $autosave
    string $jardir
 */

    private $playersCached = false;
    private $prevDaemon = 0;
    private $_mysqlInfo = false;
    public $searchOwner = '';
    public $sendData = false;

    public $deleteDir = false; ///Set to 'yes' to delete base directory when the model is deleted

    static function getJardirs($idx = false)
    {
        static $v = false;
        if (!$v)
        {
            $v = array(
                'daemon'=>Yii::t('mc', 'Daemon JAR directory'),
                'server'=>Yii::t('mc', 'Server JAR directory*'),
                'server_base'=>Yii::t('mc', 'Server base directory*'),
            );
        }
        if ($idx !== false)
            return @$v[$idx];
        return $v;
    }


    public function init()
    {
        $this->memory = '';
        $this->start_memory = '';
        $this->port = '';
    }

    public function afterFind()
    {
        $this->prevDaemon = $this->daemon_id;
    }

    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function getDbConnection()
    {
        return Yii::app()->bridgeDb;
    }

    public function tableName()
    {
        return 'server';
    }

    public function rules()
    {
        return array(
            /* User */
            array('kick_delay', 'numerical', 'integerOnly'=>true),
            array('name, world, announce_save, kick_delay', 'safe'),
            array('world', 'match', 'pattern'=>'/^[^\/\\\?%\*:"\'<>\0]+$/', 'message'=>Yii::t('mc', 'The world name contains invalid characters.')),
            array('name', 'filter', 'filter' => 'trim'),
            array('ip', 'filter', 'filter' => 'trim'),
            /* Superuser */
            array('port, players, memory, start_memory', 'numerical', 'integerOnly'=>true, 'on'=>'superuser'),
            array('port', 'numerical', 'min'=>1024, 'max'=>65535, 'on'=>'superuser'),
            array('players', 'numerical', 'min'=>1, 'on'=>'superuser'),
            array('dir', 'match', 'pattern'=>'/^[-_\w\d .!()]+$/', 'message'=>Yii::t('mc', 'The base directory contains invalid characters.'), 'on'=>'superuser'),
            array('daemon_id, ip, port, players, memory, start_memory, dir, jarfile, autostart, default_level, autosave, jardir', 'safe', 'on'=>'superuser'),
            /* Search */
            array('id, name, ip, port, dir, players, memory, start_memory, jarfile, autostart, default_level, daemon_id, announce_save, kick_delay, suspended, autosave, searchOwner, jardir', 'safe', 'on'=>'search'),
        );
    }

    public function checkDir()
    {
        if (!$this->dir)
        {
            $dir = 'server'.$this->id;
            if (!$this->dirUnique($dir))
            {
                for ($i = 97; $i <= 122; $i++)
                {
                    if ($this->dirUnique($dir.chr($i)))
                    {
                        $this->dir = $dir.chr($i);
                        break;
                    }
                }
            }
            else
                $this->dir = $dir;

            if (!$this->dir)
                $this->dir = 'server'.md5($this->id.'_'.rand());

            $this->isNewRecord = false;
            $this->saveAttributes(array('dir'));
        }
    }

    public function dirUnique($dir)
    {
        $sql = 'select `id` as id from `server` where `daemon_id`=? and `dir`=?';
        $cmd = Yii::app()->bridgeDb->createCommand($sql);
        $cmd->bindValue(1, $this->daemon_id);
        $cmd->bindValue(2, $dir);
        $cols = $cmd->queryColumn();
        return !count($cols) || (!$this->isNewRecord && count($cols) == 1
            && $cols[0]['id'] == $this->id);                
    }


    public function relations()
    {
        return array(
        );
    }

    public function attributeLabels()
    {
        return array(
            'id' => Yii::t('mc', 'ID'),
            'name' => Yii::t('mc', 'Name'),
            'ip' => Yii::t('mc', 'IP'),
            'port' => Yii::t('mc', 'Port'),
            'world' => Yii::t('mc', 'World'),
            'dir' => Yii::t('mc', 'Base directory'),
            'players' => Yii::t('mc', 'Players'),
            'memory' => Yii::t('mc', 'Memory'),
            'start_memory' => Yii::t('mc', 'Startup Memory'),
            'jarfile' => Yii::t('mc', 'JAR File'),
            'autostart' => Yii::t('mc', 'Autostart'),
            'default_level' => Yii::t('mc', 'Default Role'),
            'daemon_id' => Yii::t('mc', 'Daemon'),
            'announce_save' => Yii::t('mc', 'Announce World Save'),
            'kick_delay' => Yii::t('mc', 'Unauthorized Kick Delay'),
            'suspended' => Yii::t('mc', 'Suspended'),
            'autosave' => Yii::t('mc', 'Autosave'),
            'jardir' => Yii::t('mc', 'Look for JARs in'),
        );
    }

    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('`id`',$this->id);
        $criteria->compare('`name`',$this->name,true);
        $criteria->compare('`ip`',$this->ip,true);
        $criteria->compare('`port`',$this->port);
        $criteria->compare('`world`',$this->world,true);
        $criteria->compare('`dir`',$this->dir,true);
        $criteria->compare('`players`',$this->players);
        $criteria->compare('`memory`',$this->memory);
        $criteria->compare('`start_memory`',$this->start_memory);
        $criteria->compare('`jarfile`',$this->jarfile,true);
        $criteria->compare('`autostart`',$this->autostart);
        $criteria->compare('`default_level`',$this->default_level);
        $criteria->compare('`daemon_id`',$this->daemon_id);
        $criteria->compare('`announce_save`',$this->announce_save);
        $criteria->compare('`kick_delay`',$this->kick_delay);
        $criteria->compare('`suspended`',$this->suspended);
        $criteria->compare('`autosave`',$this->autosave);
        $criteria->compare('`jardir`',$this->jardir);

        if ($this->searchOwner)
        {
            $cr = new CDbCriteria;
            $cr->compare('`name`', $this->searchOwner, true);
            $sql = 'select `server_id` from `user_server` where `user_id` in (select `id` from `user` where '
                .$cr->condition.') and `role`=:owner';
            $cmd = Yii::app()->db->createCommand($sql);
            foreach ($cr->params as $k=>$v)
                $cmd->bindValue($k, $v);
            $cmd->bindValue(':owner', 'owner');
            $svIds = $cmd->queryColumn();
            $criteria->addInCondition('id', $svIds);
        }

        return new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,
        ));
    }

    private function generatePort()
    {
        $allIfaces = false;
        if (!strlen($this->ip) || $this->ip == '0.0.0.0')
        {
            $allIfaces = true;
            $sql = 'select max(`port`) from `server` where `daemon_id`=?';
        }
        else
            $sql = 'select max(`port`) from `server` where `daemon_id`=? and `ip`=\'\' or `ip`=\'0.0.0.0\' or `ip`=?';
        $cmd = $this->dbConnection->createCommand($sql);
        $cmd->bindValue(1, $this->daemon_id);
        if (!$allIfaces)
            $cmd->bindValue(2, $this->ip);
        $port = $cmd->queryScalar();
        $set = Setting::model()->findByPk('defaultServerPort');
        $minPort = (($set && (int)$set->value > 1024) ? (int)$set->value : 25565);
        if (!$port)
            return $minPort;
        return max($minPort, $port + 1);
    }

    public function beforeSave()
    {
        if (!$this->ip)
        {
            $set = Setting::model()->findByPk('defaultServerIp');
            if ($set && $set->value != 0)
            {
                $dmn = Daemon::model()->findByPk($this->daemon_id);
                if ($dmn)
                {
                    if ($set->value == 1)
                        $this->ip = $dmn->ip;
                    else
                        $this->ip = $dmn->ftp_ip;
                }
            }
            else
                $this->ip = '0.0.0.0';
        }
        if (!$this->port)
            $this->port = $this->generatePort();
        if (!$this->memory && $this->memory !== '0' && $this->memory !== 0)
        {
            $set = Setting::model()->findByPk('defaultServerMemory');
            if (!$set || !$set->value)
                $this->memory = 1024;
            else
                $this->memory = $set->value;
        }
        if (!$this->start_memory && $this->start_memory !== '0' && $this->start_memory !== 0)
        {
            $set = Setting::model()->findByPk('defaultServerStartMemory');
            if (!$set || !$set->value)
                $this->start_memory = 0;
            else
                $this->start_memory = $set->value;
        }
        if (!$this->players)
        {
            $set = Setting::model()->findByPk('defaultServerPlayers');
            if (!$set || !$set->value)
                $this->players = 8;
            else
                $this->players = $set->value;
        }
        if (!$this->name)
        {
            $set = Setting::model()->findByPk('defaultServerName');
            if (!$set || !$set->value)
                $this->name = Yii::t('mc', 'Minecraft Server');
            else
                $this->name = $set->value;
        }
        return true;
    }

    public function afterSave()
    {
        $this->checkDir();

        McBridge::get()->serverCmd($this->id, 'refresh', $null);
        if ($this->prevDaemon && $this->prevDaemon != $this->daemon_id)
        {
            McBridge::get()->cmd($this->prevDaemon, 'server '.$this->id.':refresh');
            $this->prevDaemon = $this->daemon_id;
        }
        return parent::afterSave();
    }

    public function beforeDelete()
    {
        $cmd = 'delinstance '.$this->id;

        if ($this->deleteDir === 'yes')
        {
            if (!McBridge::get()->serverCmd($this->id, 'get status', $status))
            {
                $this->addError('deleteDir', Yii::t('mc', 'Cannot delete base directory: ').McBridge::get()->lastError());
                return false;
            }
                
            if (@$status[0]['status'] != 'stopped')
            {
                $this->addError('deleteDir', Yii::t('mc', 'Cannot delete base directory: ')
                    .Yii::t('mc', 'Server still running'));
                return false;
            }

            $shared = Server::model()->findAllByAttributes(
                array('daemon_id'=>$this->daemon_id, 'dir'=>$this->dir));
            if (count($shared) > 1)
            {
                $this->addError('deleteDir', Yii::t('mc', 'Cannot delete base directory: ')
                    .Yii::t('mc', 'Base directory is still in use by other servers'));
                return false;
            }
            $cmd .= ' deleteDir';
         
            //Delete the database as well           
            $this->deleteDatabase();
        }
        
        McBridge::get()->cmd($this->daemon_id, $cmd);
        if ($this->prevDaemon && $this->prevDaemon != $this->daemon_id)
            McBridge::get()->cmd($this->prevDaemon, $cmd);
        return true;
    }

    public function afterDelete()
    {
        ServerConfig::model()->deleteByPk($this->id);
        UserServer::model()->deleteAllByAttributes(array('server_id'=>$this->id));
        FtpUserServer::model()->deleteAllByAttributes(array('server_id'=>$this->id));
        Command::model()->deleteAllByAttributes(array('server_id'=>$this->id));
        Schedule::model()->deleteAllByAttributes(array('server_id'=>$this->id));
        $plrs = Player::model()->findAllByAttributes(array('server_id'=>$this->id));
        foreach ($plrs as $plr)
            $plr->delete();
        return parent::afterDelete();
    }

    public function getOnlinePlayers()
    {
        if ($this->playersCached !== false)
            return $this->playersCached;
        if (!Yii::app()->user->can($this->id, 'get status')
            || !McBridge::get()->serverCmd($this->id, 'get status', $status))
            return ($this->playersCached = -2);
  
        if (@$status[0]['status'] == 'running')
            return ($this->playersCached = $status[0]['players']);
        return ($this->playersCached = -1);
    }

    public function getIpAuthRole()
    {
        $sc = ServerConfig::model()->findByPk($this->id);
        return $sc->ip_auth_role;
    }

    public function getOwner()
    {
        $sql = 'select `user_id` from `user_server` where `server_id`=? and `role`=?';
        $cmd = Yii::app()->db->createCommand($sql);
        $cmd->bindValue(1, $this->id);
        $cmd->bindValue(2, 'owner');
        return $cmd->queryScalar();
    }

    public function setOwner($id)
    {
        if (is_object($id))
            $id = $id->id;
        $prev = $this->owner;
        $res = true;

        if ($id != $prev)
            Yii::log('Changing owner of server '.$this->id.' from '.$prev.' to '.$id);
        if ($id)
        {
            $sql = 'replace into `user_server` (`user_id`,`server_id`, `role`) values(?,?,?)';
            $cmd = Yii::app()->db->createCommand($sql);
            $cmd->bindValue(1, (int)$id);
            $cmd->bindValue(2, $this->id);
            $cmd->bindValue(3, 'owner');
            $res = $cmd->execute();
        }
        //FtpUserServer::model()->deleteAllByAttributes(array('server_id'=>$this->id));
        $sql = 'update `user_server` set `role`=? where `server_id`=? and `user_id`!=? and `role`=?';
        $cmd = Yii::app()->db->createCommand($sql);
        $cmd->bindValue(1, 'admin');
        $cmd->bindValue(2, $this->id);
        $cmd->bindValue(3, $id ? (int)$id : 0);
        $cmd->bindValue(4, 'owner');
        $cmd->execute();

        if ($id != $prev && $this->sendData && Yii::app()->params['mail_assign']
            && $usr = User::model()->findByPk($id))
        {
            $msg = new YiiMailMessage;
    
            $msg->setFrom(array(Yii::app()->params['admin_email']=>Yii::app()->params['admin_name']));
            $msg->setTo(array($usr->email=>$usr->name));            

            $msg->view = 'serverAssigned';
            $msg->setBody(array(
                'server_id'=>$this->id,
                'user_id'=>$id,
                'user_name'=>$usr->name,
                'host'=>Yii::app()->request->getHostInfo(),
                'panel'=>Yii::app()->request->getBaseUrl(true),
            ));

            Yii::log('Seding assign email to '.$usr->email);
            if (!Yii::app()->mail->send($msg))
                Yii::log('Error sending assign email to '.$usr->email);
            else
                $this->sendData = false;
        }
    
        return $res;
    }

    public static function getDaemon($server)
    {
        if (is_object($server))
            $server = $server->id;
        $sql = 'select `daemon_id` from `server` where `id`=?';
        $cmd = Yii::app()->bridgeDb->createCommand($sql);
        $cmd->bindValue(1, (int)$server);
        return $cmd->queryScalar();
    }

    public function createDefaultCommands()
    {
        $cmds = array(
            array(Yii::t('mc', 'Message of the Day'), 10, 'motd',     ''),
            array(Yii::t('mc', 'Give Item'),          30, 'give',     'builtin:give'),
            array(Yii::t('mc', 'Teleport To'),        30, 'tp',       'builtin:tp'),
            array(Yii::t('mc', 'Summon Player'),      30, 'summon',   'builtin:summon'),
            array(Yii::t('mc', 'Admin Say'),          30, 'asay',     'builtin:asay'),
            array(Yii::t('mc', 'Save World'),         30, 'save',     'builtin:save'),
            array(Yii::t('mc', 'Time'),               10, 'time',     'builtin:time'),
            array(Yii::t('mc', 'Date'),               10, 'date',     'builtin:date'),
            array(Yii::t('mc', 'Player List'),        10, 'list',     'builtin:list'),
            array(Yii::t('mc', 'Say Player List'),    30, 'saylist',  'builtin:saylist'),
            array(Yii::t('mc', 'Create Backup'),      40, '',         'builtin:backup'),
            array(Yii::t('mc', 'Restart'),            40, '',         'builtin:restart'),
            array(Yii::t('mc', 'Restart if Empty'),   40, '',         'builtin:restart_empty'),
            array(Yii::t('mc', 'Stop'),               40, '',         'builtin:stop'),
            array(Yii::t('mc', 'Stop if Empty'),      40, '',         'builtin:stop_empty'),
            array(Yii::t('mc', 'Start'),              40, '',         'builtin:start'),
            array(Yii::t('mc', 'Teleport Other'),     40, '',         'builtin:tp_other'),
        );

        foreach ($cmds as $c)
        {
            $cmd = Command::model()->findByAttributes(array('server_id'=>$this->id, 'name'=>$c[0]));
            if ($cmd)
                continue;
            $cmd = new Command;
            $cmd->server_id = $this->id;
            $cmd->name      = $c[0];
            $cmd->level     = $c[1];
            $cmd->chat      = $c[2];
            $cmd->run       = $c[3];
            $cmd->save();
        }
    }


    public function suspend()
    {
        $success = false;
        if (!$this->hasAttribute('suspended'))
            $this->addError('suspended', Yii::t('mc', 'Attribute not found, database up to date?'));
        else
        {
            $this->suspended = 1;
            $success = $this->save(false);
        }
        Yii::log('Suspended server '.$this->id);
        $scheds = Schedule::model()->findAllByAttributes(array('server_id'=>$this->id,
            'status'=>array(Schedule::Scheduled, Schedule::ReScheduled)));
        foreach ($scheds as $sched)
        {
            $sched->status = Schedule::Suspended;
            $sched->save(false);
        }
        return $success;
    }

    public function resume()
    {
        $success = false;
        if (!$this->hasAttribute('suspended'))
            $this->addError('suspended', Yii::t('mc', 'Attribute not found, database up to date?'));
        else
        {
            $this->suspended = 0;
            $success = $this->save(false);
        }
        Yii::log('Resumed server '.$this->id);
        $scheds = Schedule::model()->findAllByAttributes(array('server_id'=>$this->id,
            'status'=>Schedule::Suspended));
        foreach ($scheds as $sched)
        {
            $sched->status = Schedule::Scheduled;
            $sched->save(false);
        }
        return $success;
    }


    public function getDbInfo()
    {
        $cmd = Yii::app()->db->createCommand('select `name`, `password` from `mysql_db` where `server_id`=?');
        $cmd->bindValue(1, (int)$this->id);
        $info = $cmd->queryRow(false);
        if (!$info)
            return false;
        if (!strlen(@$info[0]))
            $info[0] = $this->mysqlPrefix.((int)$this->id);
        return $info;
    }

    private function openUserDb()
    {
        if (Yii::app()->params['demo_mode'] == 'enabled')
            return new DummyDb;
        $db = new CDbConnection('mysql:host='.$this->mysqlHost,
            $this->mysqlInfo['user'], $this->mysqlInfo['pass']);
        $db->emulatePrepare = true;
        $db->charset = 'utf8';
        $db->active = true;
        return $db;
    }

    public function deleteDatabase()
    {
        $info = $this->dbInfo;
        $user = $info[0];
        Yii::log('Deleting MySQL database for server '.$this->id.': '.$user);

        if (!strlen($user))
        {
            Yii::log('No database provided');
            return false;
        }

        try
        {
            $db = $this->openUserDb();
            $db->createCommand('drop database '.$db->quoteColumnName($user))->execute();
            $db->createCommand('drop user '.$db->quoteValue($user).'@\'%\'')->execute();

            $cmd = Yii::app()->db->createCommand('delete from `mysql_db` where `server_id`=?');
            $cmd->bindValue(1, $this->id);
            $cmd->execute();

            $db->active = false;
        }
        catch (Exception $e)
        {
            Yii::log('Failed to delete MySQL database "'.$user.'"! ('.$e->getMessage().'")');
            return false;
        }
        return true;
    }

    public function createDatabase()
    {
        $user = $this->mysqlPrefix.$this->id;
        $pass = substr(md5(rand()), 0, 10);

        Yii::log('Creating MySQL database for server '.$this->id.': '.$user);

        if (!strlen($user))
        {
            Yii::log('No database provided');
            return false;
        }
        
        try
        {
            $db = $this->openUserDb();
            $db->createCommand('create database '.$db->quoteColumnName($user))->execute();
            $db->createCommand('create user '.$db->quoteValue($user).'@\'%\' identified by '
                .$db->quoteValue($pass))->execute();
            $db->createCommand('grant all privileges on '.$db->quoteColumnName($user).'.* to '
                .$db->quoteValue($user).'@\'%\'')->execute();

            $cmd = Yii::app()->db->createCommand('replace into `mysql_db` values(?,?,?)');
            $cmd->bindValue(1, $this->id);
            $cmd->bindValue(2, $user);
            $cmd->bindValue(3, $pass);
            $cmd->execute();

            $db->active = false;
        }
        catch (Exception $e)
        {
            Yii::log('Failed to create MySQL database "'.$user.'"! ('.$e->getMessage().')');
            return false;
        }
        return true;
    }

    public function changeDatabasePw()
    {
        $info = $this->dbInfo;
        $user = $info[0];
        $pass = substr(md5(rand()), 0, 10);

        Yii::log('Changing MySQL password for server '.$this->id.': '.$user);

        if (!strlen($user))
        {
            Yii::log('No database provided');
            return false;
        }
        
        try
        {
            $db = $this->openUserDb();
            $db->createCommand('set password for '.$db->quoteValue($user)
                .'@\'%\'=PASSWORD('.$db->quoteValue($pass).')')->execute();

            $cmd = Yii::app()->db->createCommand('replace into `mysql_db` values(?,?,?)');
            $cmd->bindValue(1, $this->id);
            $cmd->bindValue(2, $user);
            $cmd->bindValue(3, $pass);
            $cmd->execute();

            $db->active = false;
        }
        catch (Exception $e)
        {
            Yii::log('Failed to change MySQL password for "'.$user.'"! ('.$e->getMessage().')');
            return false;
        }
        return true;
    }

    public function getMysqlInfo()
    {
        if ($this->_mysqlInfo)
            return $this->_mysqlInfo;
        $info = @include(dirname(__FILE__).'/../config/user_databases.php');
        $info = @$info[$this->daemon_id];
        if (!is_array($info))
            $info = array('host'=>Yii::app()->params['user_mysql_host']);
        else if (!isset($info['host']))
            $info['host'] = '*';

        if (!@$info['user'])
        {
            $info['user'] = Yii::app()->params['user_mysql_user'];
            $info['pass'] = Yii::app()->params['user_mysql_pass'];
        }
        else if (!@$info['pass'])
            $info['pass'] = '';

        $dmn = Daemon::model()->findByPk($this->daemon_id);
        if ($info['host'] == '*')
        {
            if ($dmn)
                $info['host'] = $dmn->ip;
            else
                $info['host'] = false;
        }
        else if ($info['host'] == '')
            $info['host'] = false;

        if (!isset($info['prefix']))
            $info['prefix'] = Yii::app()->params['user_mysql_prefix'];

        if (!isset($info['link']))
            $info['link'] = Yii::app()->params['user_mysql_admin'];

        if ($dmn && $info['link'])
            $info['link'] = str_replace('*', $dmn->ip, $info['link']);

        return ($this->_mysqlInfo = $info);
    }


    public function getMysqlHost()
    {
        return @$this->mysqlInfo['host'];
    }

    public function getMysqlPrefix()
    {
        return @$this->mysqlInfo['prefix'];
    }

    public function getMysqlLink()
    {
        return @$this->mysqlInfo['link'];
    }
}
