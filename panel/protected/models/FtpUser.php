<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class FtpUser extends ActiveRecord
{
/*
    int $id
    string $name
    string $password
*/

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
        return 'ftp_user';
    }

    public function syncWithUser($user)
    {
        if (!is_object($user))
            $user = User::model()->findByPk((int)$user);
        if (!$user)
            return false;
        $this->name = $user->name;
        $this->password = $user->password;
        return true;
    }

    public function relations()
    {
        return array();
    }
}
