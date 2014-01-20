<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class UserIdentity extends CUserIdentity
{
    public $_id;

    public function authenticate($ignoreIp = false)
    {
        $model = User::model()->findByAttributes(array('name'=>$this->name));
        if ($model)
            $this->password = crypt($this->password, $model->password);
        if(!$model)
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        else if($model->password !== $this->password)
            $this->errorCode = self::ERROR_PASSWORD_INVALID;
        else
        {
            $this->_id = $model->id;
            $this->setState('token', md5($this->password));
            $this->setState('ip', $ignoreIp ? 'ignore' : Yii::app()->user->ip);
            $this->errorCode = self::ERROR_NONE;
        }
        return !$this->errorCode;
    }

    public function getId()
    {
        return $this->_id;
    }
}
