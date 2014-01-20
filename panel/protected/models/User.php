<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class User extends ActiveRecord
{
/*
    int $id
    string $name
    string $password
    string $email
    string $global_role
    string $api_key
    string $lang
    string $reset_hash
*/
    private $_serverRole = array();
    private $_sendPassword = false;
    public $prevName = false, $prevPassword = false;
    public $sendData = false;
    var $confirmPassword, $verifyCode;
    static function getRoleLabels($idx = false)
    {
        static $rl = false;
        if (!$rl)
        {
            $rl = array(
                Yii::t('mc', 'No Access'),
                Yii::t('mc', 'Guest'),
                Yii::t('mc', 'User'),
                Yii::t('mc', 'Moderator'),
                Yii::t('mc', 'Administrator'),
                Yii::t('mc', 'Owner')
            );
        }
        if ($idx !== false)
            return @$rl[$idx];
        return $rl;
    }
    static $roles = array(
        'none',
        'guest',
        'user',
        'mod',
        'admin',
        'owner'
    );
    static $roleLevels = array(
        0,
        10,
        20,
        30,
        40,
        50
    );

    public function afterFind()
    {
        $this->prevName = $this->name;
        $this->prevPassword = $this->password;
    }

    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'user';
    }

    public function rules()
    {
        return array(
            array('name, email', 'required'),
            array('name, email', 'filter', 'filter' => 'trim'),
            array('name, email', 'unique'),
            array('email','email'),
            array('password, confirmPassword', 'required', 'on'=>'register, reset'),
            array('confirmPassword', 'compare', 'compareAttribute'=>'password', 'on'=>'register, reset'),
            array('name, password, confirmPassword, email', 'length', 'max'=>128),
            array('name, password, confirmPassword, email', 'safe', 'on'=>'register'),
            array('password', 'length', 'min'=>(int)@Yii::app()->params['min_pw_length'], 'on'=>'register'),
            array('lang', 'checkLang'),
            array('name', 'unsafe', 'on'=>'update'),
            array('global_role', 'safe', 'on'=>'superuserUpdate'),
            array('id, name, email, global_role, lang', 'safe', 'on'=>'search'),
            array('name', 'safe', 'on'=>'userSearch'),
            array('id, email, global_role, lang, password', 'unsafe', 'on'=>'userSearch'),
            array('verifyCode', 'captcha', 'allowEmpty'=>!CCaptcha::checkRequirements(), 'on'=>'register'),
        );
    }

    public function checkLang()
    {
        if (!Yii::app()->controller || !method_exists(Yii::app()->controller, 'languageSelection'))
            return;
        $ls = Yii::app()->controller->languageSelection();
        if (!isset($ls[$this->lang]))
            $this->addError('lang', Yii::t('mc', 'Invalid language selected'));
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
            'name' => Yii::t('mc', 'Username'),
            'password' => Yii::t('mc', 'Password'),
            'confirmPassword' => Yii::t('mc', 'Confirm Password'),
            'email' => Yii::t('mc', 'Email'),
            'verifyCode' => Yii::t('mc', 'Verification Code'),
            'global_role' => Yii::t('mc', 'Global Role'),
            'api_key' => Yii::t('mc', 'API Key'),
            'lang' => Yii::t('mc', 'Language'),
            'reset_hash' => Yii::t('mc', 'Password Reset Token'),
        );
    }

    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('`id`',$this->id);
        $criteria->compare('`name`',$this->name,true);
        $criteria->compare('`password`',$this->password,true);
        $criteria->compare('`email`',$this->email,true);
        $criteria->compare('`global_role`',$this->global_role,true);
        $criteria->compare('`api_key`',$this->api_key,true);
        $criteria->compare('`lang`',$this->lang, true);
        $criteria->compare('`reset_hash`',$this->reset_hash);

        return new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,
        ));
    }

    public function getServerFtpAccess($server)
    {
        $ftpUser = FtpUser::model()->findByAttributes(array('name'=>$this->name));
        if (!$ftpUser)
            return '';
        $access = FtpUserServer::model()->findByAttributes(array('user_id'=>$ftpUser->id, 'server_id'=>$server));
        if (!$access)
            return '';
        if ($access->perms == 'elradfmw')
            return 'rw';
        if ($access->perms == 'elr')
            return 'ro';
        return '';
    }

    public function setServerFtpAccess($server, $accessMode)
    {
        $ftpUser = FtpUser::model()->findByAttributes(array('name'=>$this->name));
        if (!$ftpUser)
        {
            $ftpUser = new FtpUser();
            $ftpUser->syncWithUser($this);
            if (!$ftpUser->save())
                return false;
        }
        $access = FtpUserServer::model()->findByAttributes(array('user_id'=>$ftpUser->id, 'server_id'=>$server));
        if (!$access)
        {
            $access = new FtpUserServer();
            $access->user_id = $ftpUser->id;
            $access->server_id = $server;
        }
        if ($accessMode == 'rw')
            $access->perms = 'elradfmw';
        else if ($accessMode == 'ro')
            $access->perms = 'elr';
        else
            $access->perms = '';
        return $access->save();
    }

    public function getServerRole($server)
    {
        $server = (int)$server;
        if (isset($this->_serverRole[$server]))
            return $this->_serverRole[$server];
        $sql = 'select `role` from `user_server` where `server_id`=? and `user_id`=?';
        $cmd = $this->getDbConnection()->createCommand($sql);
        $cmd->bindValue(1, $server);
        $cmd->bindValue(2, $this->id);
        $role = '';
        if (($row=$cmd->queryRow()) === false)
            return '';
        return ($this->_serverRole[$server] = $row['role']);
    }

    public function setServerRole($server, $role)
    {
        $server = (int)$server;
        $this->_serverRole[$server] = $role;
        $sql = 'replace into `user_server` (`user_id`, `server_id`, `role`) values(:u,:s,:r)';
        $cmd = $this->getDbConnection()->createCommand($sql);
        $cmd->bindValue(':u', $this->id);
        $cmd->bindValue(':s', $server);
        $cmd->bindValue(':r', $role);
        $res = false;
        try
        {
            $res = $cmd->execute();
        }
        catch(Exception $e)
        {
            return false;
        }
        return $res;
    }

    public function getLevel($server)
    {
        $role = $this->getServerRole($server);
        if (!$role)
            return 1;
        return User::getRoleLevel($role);
    }

    public static function getRoleLabel($role)
    {
        if ($role == 'superuser')
            return Yii::t('mc', 'Superuser');
        $idx = array_search($role, User::$roles);
        if ($idx === false)
            return Yii::t('mc', 'No Access');
        return User::getRoleLabels($idx);
    }

    public static function getRoleLevel($role)
    {
        $idx = array_search($role, User::$roles);
        if ($idx === false)
            return 0;
        return $idx * 10;
    }

    public static function getLevelRole($level)
    {
        $idx = 0;
        $cnt = count(User::$roleLevels);
        $role = '';
        for ($i = 0; $i < $cnt; $i++)
            if ($level >= User::$roleLevels[$i])
                $role = User::$roles[$i];
            else
                break;
        return $role;
    }

    public function beforeSave()
    {
        if (@strlen($this->prevPassword) && ($this->prevPassword == crypt($this->password, $this->prevPassword)
            || $this->password == $this->prevPassword))
        {
            $this->password = $this->prevPassword;
            //Password is the same
            return true;
        }

        if (!@strlen(trim($this->password)) && @strlen($this->prevPassword))
        {
            $this->password = $this->prevPassword;
            //New password is empty, ignore
            return true;
        }

        if (Yii::app()->params['mail_welcome'] && Yii::app()->user->isSuperuser()
            && Yii::app()->user->id != $this->id && Yii::app()->user->superuser != $this->name)
        {
            $this->_sendPassword = $this->password;
        }

        //http://www.php.net/manual/en/function.crypt.php#93171
        $base64_alphabet='ABCDEFGHIJKLMNOPQRSTUVWXYZ'
            .'abcdefghijklmnopqrstuvwxyz0123456789+/';
        $salt = '$1$';
        for ($i = 0; $i < 9; $i++)
            $salt .= $base64_alphabet[rand(0,63)];

        $this->password = crypt($this->password, $salt.'$');

        return true;
    }

    public function afterSave()
    {
        $name = $this->name;
        if (@strlen($this->prevName) && $this->prevName != $name)
            $name = $this->prevName;
        $ftpUser = FtpUser::model()->findByAttributes(array('name'=>$name));
        if ($ftpUser)
        {
            $ftpUser->syncWithUser($this);
            $ftpUser->save();
        }
        if ($this->sendData && $this->_sendPassword !== false)
        {
            $msg = new YiiMailMessage;
    
            $msg->setFrom(array(Yii::app()->params['admin_email']=>Yii::app()->params['admin_name']));
            $msg->setTo(array($this->email=>$this->name));            

            if ($this->isNewRecord)
                $msg->view = 'welcome';
            else
                $msg->view = 'password';
            $msg->setBody(array(
                'password'=>$this->_sendPassword,
                'id'=>$this->id,
                'name'=>$this->name,
                'email'=>$this->email,
                'host'=>Yii::app()->request->getHostInfo(),
                'panel'=>Yii::app()->request->getBaseUrl(true),
            ));

            Yii::log('Seding welcome email to '.$this->email);
            if (!Yii::app()->mail->send($msg))
                Yii::log('Error sending welcome email to '.$this->email);
            else
            {
                $this->_sendPassword = false;
                $this->sendData = false;
            }
        }
        return parent::afterSave();
    }   

    public function afterDelete()
    {
        UserServer::model()->deleteAllByAttributes(array('user_id'=>$this->id));
        $ftpUser = FtpUser::model()->findByAttributes(array('name'=>$this->name));
        if ($ftpUser)
        {
            FtpUserServer::model()->deleteAllByAttributes(array('user_id'=>$ftpUser->id));
            $ftpUser->delete();
        }
        return parent::afterDelete();
    }
}
