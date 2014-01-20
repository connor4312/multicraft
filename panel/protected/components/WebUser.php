<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class WebUser extends CWebUser
{
    public $ip;
    private $_superuser = null;
    private $_globalRole = null;
    private $_serverRole = array();
    private $_model = null;

    public function init()
    {
        parent::init();
        //Normalize user IP
        $this->ip = $_SERVER['REMOTE_ADDR'];
        $this->ip = preg_replace('/^[^\d]*/', '', $this->ip);

        if (!$this->getIsGuest())
        {
            if (md5($this->model->password) != $this->getState('token'))
            {
                Yii::log('Invalid cookie token for '.$this->name.'. Logging out.');
                $this->logout(true);
                Yii::app()->getRequest()->redirect(CHtml::normalizeUrl(array('/site')));
            }
            $stateIp = $this->getState('ip');
            if ($this->ip != $stateIp && $stateIp != 'ignore')
            {
                Yii::log('Invalid cookie IP for '.$this->name.'. Logging out.');
                $this->logout(true);
                Yii::app()->getRequest()->redirect(CHtml::normalizeUrl(array('/site')));
            }
            if (@Yii::app()->params['installer'] != 'show' && isset($this->model->lang)
                && preg_match('/^[a-zA-Z_]+$/', $this->model->lang))
                Yii::app()->language = $this->model->lang;
        }
        if ($this->isSuperuser() && @strlen(Yii::app()->params['admin_ips']))
        {
            if (!$this->checkIp(Yii::app()->params['admin_ips']))
            {
                Yii::log('Superuser access denied for '.$this->name.' (IP '.$this->ip.')');
                $this->logout(true);
                Yii::app()->getRequest()->redirect(CHtml::normalizeUrl(array('/site')));
            }
        }
    }

    public function apiLogin($user)
    {
        if (@strlen(Yii::app()->params['api_ips']) && !$this->checkIp(Yii::app()->params['api_ips']))
        {
            Yii::log('API access denied for '.$user->name.' (IP '.$this->ip.')');
            return false;
        }
        $ident = new UserIdentity($user->name, '');
        $ident->_id = $user->id;
        $ident->errorCode = UserIdentity::ERROR_NONE;

        return $this->login($ident, 0);
    }

    public function login($identity, $duration = 0)
    {
        if (@strlen(Yii::app()->params['admin_ips']))
        {
            $model = User::model()->findByPk($identity->id);
            if ($model->global_role == 'superuser' || $model->name == $this->superuser)
            {
                if (!$this->checkIp(Yii::app()->params['admin_ips']))
                {
                    Yii::log('Superuser login denied for '.$model->name.' (IP '.$this->ip.')');
                    return false;
                }
            }
        }
        return parent::login($identity, $duration);
    }

    public function checkIp($ipListStr)
    {
        $ips = @explode(',', $ipListStr);
        if (!is_array($ips))
            $ips = array();

        foreach ($ips as $ip)
        {
            if (trim($ip) === $this->ip)
                return true;
        }
        return false;
    }

    public function getModel()
    {
        if (!$this->_model)
        {
            try
            {
                $this->_model = User::model()->findByPk((int)$this->id);
            }
            catch (Exception $e)
            {
                Yii::log('Error getting model for '.$this->name.': '.$e->getMessage().'. Logging out.');
            }
        }
        if (!$this->_model)
        {
            $this->logout(true);
            Yii::app()->getRequest()->redirect(CHtml::normalizeUrl(array('/site')));
        }
        $this->name = $this->_model->name;
        return $this->_model;
    }
    
    /**
     * Throw an exception and show an error message
     */
    public function deny($message = false, $reason = false)
    {
        Yii::log('Access denied for '.$this->name.': '.$message.' '.$reason);
        if ($this->isGuest)
            $this->loginRequired();
        else
            throw new CHttpException(403, ($message ? $message : Yii::t('mc', 'Access denied!')).($reason ? ' '.$reason : ''));
    }

    public function getSuperuser()
    {
        if (!$this->_superuser)
        {
            $this->_superuser = Yii::app()->params['superuser'];
            if (!$this->_superuser)
                $this->_superuser = 'admin';
        }
        return $this->_superuser;
    }

    public function isSuperuser()
    {
        if ($this->name === $this->superuser)
            return true;
        return ($this->globalRole === 'superuser');
    }

    public function getGlobalRole()
    {
        if ($this->isGuest)
            return 'none';
        if ($this->_globalRole)
            return $this->_globalRole;
        $this->_globalRole = $this->model->global_role;
        return $this->_globalRole ? $this->_globalRole : 'none';
    }

    /**
     * Check that no user can control the server if it is suspended
     */
    private function checkSvRole($sv, $role)
    {
        if ($sv && $sv->hasAttribute('suspended') && $sv->suspended)
        {
            if (User::getRoleLevel($role) > User::getRoleLevel('user'))
                $role = 'user';
        }
        return $role;
    }

    /**
     * Returns the current users role for the specified server
     */
    public function serverRole($server)
    {
        if (isset($this->_serverRole[$server]))
            return $this->_serverRole[$server];
        if ($this->isSuperuser())
            return 'owner';
        $sv = Server::model()->findByPk((int)$server);
        $role = $sv ? User::getLevelRole($sv->default_level) : '';
        if (!$this->isGuest)
        {
            if ($r = $this->model->getServerRole($server))
                $role = $r;
            if (User::getRoleLevel($role) < User::getRoleLevel($this->globalRole))
                $role = $this->globalRole;
        }
        return ($this->_serverRole[$server] = $this->checkSvRole($sv, $role));
    }

    /**
     * Returns the current users role if it corresponds to the specified player
     */
    private function playerRole($player, $serverRole)
    {
        if ($this->isGuest)
        {
            if ($player->status == 'online' && $this->ip == $player->ip)
            {
                $sv = Server::model()->findByPk($player->server_id);
                $role = $sv ? $sv->getIpAuthRole() : 'none';
                return $this->checkSvRole($sv, $role);
            }
        }
        else if ($player->user == $this->id)
            return $serverRole;
        return '';
    }

    /**
     * Check if the current user has the permissions for $action. If $self is
     * true this means that the action is to be performed on the player
     * corresponding to the current user (checks 'self $action')
     */
    private function _can($role, $action, $server, $self)
    {
        if ($self)
        {
            $sc = ServerConfig::model()->findByPk((int)$server);
            if ($sc && isset($sc->{$action.'_role'}))
            {
                $svIdx = array_search($sc->{$action.'_role'}, User::$roles);
                $plIdx = array_search($role, User::$roles);
                return $plIdx >= $svIdx;
            }
            $action = 'self '.$action;
        }
            
        $res = false;
        switch ($role)
        {
        case 'owner':
        case 'admin':
            $res |= in_array($action, array(
                    'control',
                    'stop',
                    'restart',
                    'manage players',
                    'edit',
                    'manage commands',
                    'start backup',
                    'command',
                    'edit configs',
                    'clear chat',
                    'clear log',
                ));
        case 'mod':
            $res |= in_array($action, array(
                    'start',
                    'asay',
                    'get log',
                    'player details',
                    'summon',
                    'give',
                    'tp',
                    'kick',
                    'get backup',
                ));
        case 'user':
            $res |= in_array($action, array(
                    'self give',
                    'self tp',
                    'get chat',
                    'chat',
                    'view player',
                    'view command',
                ));
        case 'guest':
            $res |= in_array($action, array(
                    'get status',
                    'get players',
                    'view',
                    'self view player',
                ));
        default:
        }
        return $res;
    }

    /**
     * Checks if the user can perform $action on $server
     */
    public function can($server, $action, $autoDeny = false, $message = false)
    {
        if (!$server && !$this->isSuperuser())
        {
            if ($autoDeny)
                $this->deny($message, Yii::t('mc', '(no server)'));
            return false;
        }   
        $role = $this->serverRole($server);
        if (!($res = $this->_can($role, $action, $server, false)) && $autoDeny)
        {
            $this->deny($message, Yii::t('mc', '(action "{action}" for "{role}")',
                array('{action}'=>$action, '{role}'=>User::getRoleLabel($role))));
        }
        return $res;
    }

    /**
     * Checks if the user can perform $action for $player
     */
    public function canSelf($player, $action, $autoDeny = false, $message = false)
    {
        if (!$player || !$player->server_id)
        {
            if ($autoDeny)
                $this->deny($message, Yii::t('mc', '(no player)'));
            return false;
        }   
    
        $role = $this->serverRole($player->server_id);
        if ($this->_can($role, $action, $player->server_id, false))
            return true;

        $role = $this->playerRole($player, $role);
        if (!($res = $this->_can($role, $action, $player->server_id, true)) && $autoDeny)
        {
            $this->deny($message, Yii::t('mc', '(self action "{action}" for "{role}")',
                array('{action}'=>$action, '{role}'=>User::getRoleLabel($role))));
        }
        return $res;        
    }
}
