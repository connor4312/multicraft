<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class LoginForm extends CFormModel
{
    public $name;
    public $password;
    public $rememberMe;
    public $ignoreIp;

    private $_identity;

    public function init()
    {
        parent::init();
        $this->ignoreIp = @Yii::app()->params['default_ignore_ip'];
    }

    public function rules()
    {
        return array(
            array('name, password', 'required'),
            array('rememberMe', 'boolean'),
            array('ignoreIp', 'boolean'),
            array('password', 'authenticate'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'name'=>Yii::t('mc', 'Name'),
            'password'=>Yii::t('mc', 'Password'),
            'rememberMe'=>Yii::t('mc', 'Stay logged in'),
            'ignoreIp'=>Yii::t('mc', 'Allow IP changes'),
        );
    }

    public function authenticate($attribute,$params)
    {
        if(!$this->hasErrors())
        {
            $this->_identity=new UserIdentity($this->name, $this->password);
            if(!$this->_identity->authenticate($this->ignoreIp))
                $this->addError('password',Yii::t('mc', 'Incorrect username or password.'));
        }
    }

    public function login()
    {
        if($this->_identity===null)
        {
            $this->_identity=new UserIdentity($this->name,$this->password);
            $this->_identity->authenticate($this->ignoreIp);
        }
        if($this->_identity->errorCode===UserIdentity::ERROR_NONE)
        {
            $duration=$this->rememberMe ? 3600*24*30 : 0; // 30 days
            Yii::app()->user->login($this->_identity,$duration);
            return true;
        }
        else
            return false;
    }
}
