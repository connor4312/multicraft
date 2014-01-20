<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class McBridge extends McErrors
{
    public $autoconnect;
    public $socketTimeout;

    private $connections = array();
    private static $inst = null;
    private $daemonIds = false;
    private $passwords = array();

    private function __construct($autoconnect = true)
    {
        $this->socketTimeout = Yii::app()->params['timeout'];
        $this->autoconnect = $autoconnect;
        $this->passwords = @include(dirname(__FILE__).'/../config/daemons.php');
        if (!is_array($this->passwords))
            $this->passwords = array();
        $this->passwords['default'] = array('password' => Yii::app()->params['daemon_password']);
    }
    
    static function get()
    {
        if (Yii::app()->params['demo_mode'] == 'enabled')
            McBridge::$inst = McBridgeDemo::get();
        if (!McBridge::$inst)
            McBridge::$inst = new McBridge();
        return McBridge::$inst;
    }

    public function conStrings()
    {
        $c = array();
        $ids = $this->getDaemonIds(); 
        foreach ($ids as $id)
        {
            $con = $this->getConnection($id);
            if (!$con)
                continue;
            $c[$id] = 'ID '.$id.' - '.$con->name.' ('.$con->ip.')';
        }
        return $c;
    }

    public function getConnection($id)
    {
        if (@isset($this->connections[$id]))
            return $this->connections[$id];
        $daemon = Daemon::model()->findByPk((int)$id);
        if (!$daemon)
            return null;
        $pw = isset($this->passwords[$id]['password']) ? $this->passwords[$id]['password']
            : @$this->passwords['default']['password'];
        $con = new McConnection($this, $id, $daemon->name, $daemon->ip, $daemon->port, $pw);
        return ($this->connections[$id] = $con);
    }

    public function getDaemonIds()
    {
        if (!$this->daemonIds)
        {
            $cmd = Daemon::model()->getDbConnection()->createCommand('select `id` from `daemon`');
            $this->daemonIds = $cmd->queryColumn();
        }
        return $this->daemonIds;
    }

    public function connectionCount()
    {
        return count($this->getDaemonIds());   
    }

    public function serverCmd($server, $cmd, &$data = null, $broadcast = false, $nocache = false)
    {
        $command = $cmd;
        $r = array();
        if (($cache = CommandCache::get($server, $command, $r)) === 1)
        {
            if (@$r['success'])
            {
                $data = @$r['data'];
                return true;
            }
            $this->addError(@$r['error']);
            return false;
        }
        $cmd = 'server '.$server.':'.$cmd;
        $ret = array();
        if ($broadcast)
            $ret = $this->globalCmd($cmd);
        else
        {
            $ret = array($this->cmd(Server::getDaemon($server), $cmd));
        }
        $e = '';
        foreach ($ret as $r)
        {
            if ($cache !== 0)
                CommandCache::set($server, $command, $r);
            if ($r['success'])
            {
                $data = $r['data'];
                return true;
            }
            $e = $r['error'];
        }
        $this->addError($e);
        return false;
    }

    public function globalCmd($cmd)
    {
        Yii::log('Sending command "'.$cmd.'" to all daemons');
        $this->clearErrors();
        $ret = array();
        $ids = $this->getDaemonIds();
        foreach ($ids as $id)
        {
            $con = $this->getConnection($id);
            if (!$con)
                continue;
            $ret[$id] = array();
            $d = array();
            $ret[$id]['success'] = $con->command($cmd, $d);
            $ret[$id]['data'] = $d;
            $ret[$id]['error'] = $con->lastError();
        }
        return $ret;
    }

    public function cmd($daemon, $cmd)
    {
        if (!preg_match('/^(server\s+\d+\s*:(get\s|plugin has|backup (status|list))|updatejar (list|status)|cfgfile (check|get)|version)/', $cmd))
            Yii::log('Sending command "'.$cmd.'" to daemon '.$daemon);
        $this->clearErrors();
        $ret = array();
        $con = $this->getConnection($daemon);
        if (!$con)
        {
            $ret['success'] = false;
            $ret['data'] = '';
            $ret['error'] = Yii::t('mc', 'No connection for daemon {id}', array('{id}'=>$daemon));
            return $ret;
        }
        $d = array();
        $ret['success'] = $con->command($cmd, $d);
        $ret['data'] = $d;
        $ret['error'] = $con->lastError();
        return $ret;
    }

    static function parse($data)
    {
        if (!$data)
            return array();
        if (!is_array($data))
            $data = array($data);

        $ret = array();
        foreach ($data as $line)
        {
            $items = preg_split('/ :/', $line);
            $data = array();
            for ($i = 0; ($i + 1) < count($items); $i += 2)
                $data[$items[$i]] = preg_replace('/\\\\\\\\/', '\\', preg_replace('/ \\\:/', ' :', $items[$i+1]));
            $ret[] = $data;
        }
        return $ret;
    }

}

class McConnection extends McErrors
{
    var $id;
    var $name;
    var $ip;
    var $port;
    var $password;
    var $socket;
    var $socketTimeout;
    var $bridge;
    var $triedConnect = false;
    var $connectError = '';

    function __construct($bridge, $id, $name, $ip, $port, $password)
    {
        $this->bridge = $bridge;
        $this->id = $id;
        $this->name = $name;
        $this->ip = $ip;
        $this->port = $port;
        $this->password = $password;
        $this->socketTimeout = $bridge->socketTimeout;
        $this->socket = false;        
    }

    function command($cmd, &$data)
    {
        $cmd = str_replace("\n", " ", $cmd);
        if (!$this->send($cmd))
            return false;
        $r = $this->recv();
        if (!$r['ack'])
            return false;
        $data = McBridge::parse($r['data']);
        return true;
    }
    public function connect()
    {
        if ($this->triedConnect)
        {
            $this->addError($this->connectError);
            return false;
        }
        $this->triedConnect = true;
        $this->connectError = '';
        $errno = 0; $errstr = '';
        $this->socket = @pfsockopen($this->ip, $this->port, $errno, $errstr, $this->socketTimeout);
        if (!$this->socket)
        {
            $this->connectError = Yii::t('mc', 'Can\'t connect to Minecraft bridge! ({errno}: {errstr})',
                array('{errno}'=>$errno, '{errstr}'=>$errstr));
            $this->addError($this->connectError);
            $this->socket = false;
            return false;
        }
        stream_set_timeout($this->socket, $this->socketTimeout);

        //clear stream (we're using persistent connection)
        while ($this->dataReady())
            if (!fgets($this->socket))
                break;
        if (!$this->connected())
        {   
            $this->connectError = Yii::t('mc', 'Can\'t connect to Minecraft bridge! (Connection lost)');
            $this->addError($this->connectError);
            $this->socket = false;
            return false;
        }
        return true;
    }

    public function auth()
    {
        $data;
        if (!$this->command('auth', $data))
        {
            $this->disconnect();
            $this->addError(Yii::t('mc', 'Authentication failed! (auth: {error})',
                array('{error}'=>$this->lastError())));
            return false;
        }
        $token = @$data[0]['token'];
        if (preg_match('/^([0-9]+)/', $token))
        {
            $code = base64_encode(sha1($token.sha1(sha1($this->password))) ^ sha1($this->password));
            //echo "CODE: $code";
            if (!$this->command('codeword: '.$code, $none))
            {
                $this->disconnect();
                $this->addError(Yii::t('mc', 'Authentication failed! (code: {error})',
                    array('{error}'=>$this->lastError())));
                return false;
            }
        }
        return true;
    }

    public function connected()
    {
        return $this->socket !== false;
    }
    
    public function dataReady()
    {
        if (!$this->connected())
            return false;
        return @stream_select($r = array($this->socket), $w = null, $x = null, 0) > 0;
    }

    public function send($data)
    {
        if (!$this->connected())
        {
            if (!$this->bridge->autoconnect)
            {
                $this->addError(Yii::t('mc', 'Not connected!'));
                return false;
            }
            if (!$this->connect() || !$this->auth())
               return false; 
        }
        if (@fwrite($this->socket, $data."\n") === false)
        {
            $this->addError(Yii::t('mc', 'Send failed!'));
            return false;
        }
        return true;
    }

    public function recv()
    {
        if (!$this->connected())
        {
            $this->addError(Yii::t('mc', 'Not connected!'));
            return false;
        }
        $ret = array();
        $ret['ack'] = false;
        $ret['error'] = Yii::t('mc', 'Data receive timeout');
        $ret['data'] = array();

        $prev = '';
        while(true)
        {
            $r = fgets($this->socket);
            $data = $prev.$r;
            $prev = $data;
            if ($r && $data[strlen($data)-1] != "\n")
                continue;
            if (strlen($data) && $data[0] == '>')
            {
                if ($data[1] != 'O')
                {
                    $ret['error'] = preg_replace('/ERROR( - )?/', '', substr($data, 1, strlen($data) - 2));
                    //$this->addError($ret['error']);
                }
                else
                {
                    $ret['ack'] = true;
                    $ret['error'] = false;
                }
                if ($this->dataReady())
                {
                    //We somehow have a second response on the stream, discard current response
                    $ret['ack'] = false;
                    $ret['error'] = false;
                    $ret['data'] = array();
                    $prev = '';
                }
                else
                    break;
            }
            else if (!$data)
            {
                if (!$ret['ack'])
                    $ret['error'] = Yii::t('mc', 'Empty response');
                break;
            }
            else
            {
                $prev = '';
                $ret['data'][] = substr($data, 1, strlen($data) - 2);
            }
        }
        if (!$ret['ack'])
            $this->addError($ret['error']);
        return $ret;
    }

    public function disconnect()
    {
        if (!$this->connected())
            return;
        fclose($this->socket);
        $this->socket = false;
        $this->triedConnect = false;
    }
}

class McErrors
{
    var $_errors = array();

    public function addError($error)
    {
        $this->_errors[] = $error;
    }

    public function errors()
    {
        return $this->_errors;
    }

    public function lastError()
    {
        $c = count($this->_errors);
        if (!$c)
            return false;
        return $this->_errors[$c - 1];
    }

    public function showErrors()
    {
        foreach ($this->_errors as $error)
            echo $error.'<br/>';
    }

    public function clearErrors()
    {
        $this->errors = array();
    }
}
