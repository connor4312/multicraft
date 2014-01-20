<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class FtpUserServer extends ActiveRecord
{
/*
    int $user_id
    int $server_id
    string $perms
*/

    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'ftp_user_server';
    }

    public function getDbConnection()
    {
        return Yii::app()->bridgeDb;
    }
}
