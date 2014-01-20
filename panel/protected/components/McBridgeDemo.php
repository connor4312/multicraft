<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class McBridgeDemo extends McBridge
{
    public $responses = array();
    private static $inst = null;
    private static $con = null;
    private $connections = array();
    
    private function __construct()
    {
    }
    
    static function get()
    {
        if (!McBridgeDemo::$inst)
            McBridgeDemo::$inst = new McBridgeDemo();
        return McBridgeDemo::$inst;
    }

    public function getConnection($id)
    {
        if (@isset($this->connections[$id]))
            return $this->connections[$id];
        $daemon = Daemon::model()->findByPk((int)$id);
        if (!$daemon)
            return null;
        $con = new McConnectionDemo($this, $id, $daemon->name, $daemon->ip, $daemon->port, '');
        return ($this->connections[$id] = $con);
    }
}

class McConnectionDemo extends McConnection
{
    public function command($cmd, &$data)
    {
        $r = array();
        $dis = Yii::t('mc', 'Function disabled in demo mode!');
        $fail = false;
        $cmd = preg_replace('/^server [\\w\\d]+:/', '', $cmd);
        if ($cmd == 'get status')
            $r[] = 'status :running :players :5 :maxPlayers :16';
        else if ($cmd == 'get players')
        {
            $r[] = 'id :1 :name :Player1 :ip :10.0.0.2';
            $r[] = 'id :2 :name :Player2 :ip :10.0.0.3';
            $r[] = 'id :3 :name :Player3 :ip :10.0.0.4';
            $r[] = 'id :4 :name :Player4 :ip :10.0.0.5';
            $r[] = 'id :5 :name :Player5 :ip :10.0.0.6';
        }
        else if ($cmd == 'get chat')
        {
            $r[] = 'time :0 :name :Player1 :text :Hi All';
            $r[] = 'time :2 :name :Player2 :text :Hey!';
        }
        else if ($cmd == 'get log')
        {
            $r[] = 'line :14.12 16:25:31 [SERVER] INFO Loading properties';
            $r[] = 'line :14.12 16:25:31 [SERVER] INFO Starting Minecraft server on 0.0.0.0:25565';
            $r[] = 'line :14.12 16:25:32 [STARTUP] Done! For help, type "help" or "?"';
        }
        else if ($cmd == 'refresh' || $cmd == 'reload player' || $cmd == 'reload command')
            $r[] = '';
        else if (preg_match('/updatejar/', $cmd))
        {
            if (preg_match('/updatejar list/', $cmd))
            {
                $r[] = 'name :Default Minecraft Server :jar :minecraft_server.jar';
                $r[] = 'name :Mod: Craftbukkit :jar :craftbukkit.jar';
                $r[] = 'name :Mod: Craftbukkit Beta :jar :craftbukkit_beta.jar';
                $r[] = 'name :Optimized Minecraft Server :jar :minecraft_optimized.jar';
            }
            else
            {
                $r[] = 'status :done :message :'.$dis;
                $fail = true;
            }
        }
        else if (preg_match('/(deletejar|downloadjar)/', $cmd))
        {
            $r[] = 'status :done :message :'.$dis;
            $fail = true;
        }
        else if ($cmd == 'version')
        {
            $r[] = 'version :'.Yii::app()->controller->version.' :remote :'.Yii::app()->controller->version.' :time :'.time();
        }
        else if (preg_match('/backup/', $cmd))
            $fail = true;
        else if (preg_match('/server all:/', $cmd))
            $fail = true;
        else if (preg_match('/cfgfile check/', $cmd))
        {
            $m = array();
            if (preg_match('/cfgfile check:([-_.\\w\\d]+)/', $cmd, $m))
                $r[] = 'valid :True :ro :False :file :'.$m[1].' :dir :';
        }
        else if ($m = preg_match('/cfgfile get/', $cmd))
        {
            $m = preg_match('/cfgfile get:([-_.\\w\\d]+)/', $cmd);
            if (preg_match('/server.properties/', $cmd))
                $r[] = 'option :spawn-monsters :value :';
            else
                $r[] = 'line :Player1';
        }
        else if (preg_match('/cfgfile set/', $cmd))
        {
            $r[] = 'accepted :False :message :'.$dis;
            $fail = true;
        }
        else if (preg_match('/run_s:.*/', $cmd))
            $fail = true;
        else if (preg_match('/get resources/', $cmd))
            $r[] = 'pid :123 :cpu :15 :memory :45';
        else if (preg_match('/plugin has/', $cmd))
            $r[] = 'has :1';
        else if (preg_match('/plugin (add|remove)/', $cmd))
           $fail = true;
        else if (preg_match('/plugin list/', $cmd))
        {
            $r[] = 'file :permissions.jar :desc :Permissions Plugin :status :installed';
            $r[] = 'file :worldedit.jar :desc :WorldEdit Plugin :status :installed';
            $r[] = 'file :lockedchests.jar :desc :Locked Chests Plugin :status :installed';
            $r[] = 'file :lowgravity.jar :desc :Low Gravity Plugin :status :outdated';
            $r[] = 'file :jail.jar :desc :Jail Plugin :status :none';
        }
        else if (in_array($cmd, array('start', 'stop', 'restart')) || preg_match('/mc:/', $cmd))
            $fail = true;
        else if (preg_match('/^(load|delinstance) /', $cmd))
            $r[] = '';
        else
            die($dis." |$cmd|");

        if ($fail)
        {
            if (!count($r))
                $r[] = 'message :'.$dis;
            $this->addError($dis);
            return false;
        }
        $data = McBridge::parse($r);
        return true;
    }

    public function connected()
    {
        return true;
    }
    
    public function dataReady()
    {
        return false;
    }

    public function send($data)
    {
        return true;
    }

    public function recv()
    {
        $ret = array();
        $ret['ack'] = true;
        $ret['error'] = false;
        $ret['data'] = array_pop($this->responses);
        return $ret;
    }

    public function disconnect()
    {
    }

    public function connect()
    {
        return true;
    }

    public function auth()
    {
        return true;
    }

}
