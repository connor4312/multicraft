<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class ServerConfig extends ActiveRecord
{
/*
    int $server_id
    string $ip_auth_role
    string $give_role
    string $tp_role
    string $summon_role
    string $chat_role
    int $user_jar
    int $user_ftp
    int $visible
    int $user_schedule
    int $user_name
    int $user_visibility
    string $display_ip
    int $user_players
*/

    static function getVisibility($idx = false)
    {
        static $v = false;
        if (!$v)
        {
            $v = array(
                Yii::t('mc', 'Owner only'),
                Yii::t('mc', 'By Default Role'),
                Yii::t('mc', 'Users with Roles only'),
            );
        }
        if ($idx !== false)
            return @$v[$idx];
        return $v;
    }

    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'server_config';
    }

    public function rules()
    {
        return array(
            array('server_id', 'unique', 'on'=>'create'),
            array('ip_auth_role, give_role, tp_role, summon_role, chat_role', 'safe'),
            array('visible, user_jar, user_ftp, user_schedule, user_name, user_visibility, display_ip, user_players, user_mysql', 'safe', 'on'=>'superuser'),
            array('server_id, ip_auth_role, give_role, tp_role, summon_role, chat_role, user_jar, user_schedule, visible, user_name, user_visibility, display_ip, user_players, user_mysql', 'safe', 'on'=>'search'),
        );
    }

    public function relations()
    {
        return array(
        );
    }

    public function attributeLabels()
    {
        return array(
            'server_id' => Yii::t('mc', 'Server'),
            'ip_auth_role' => Yii::t('mc', 'IP Auth Role'),
            'give_role' => Yii::t('mc', 'Give Role'),
            'tp_role' => Yii::t('mc', 'Teleport Role'),
            'summon_role' => Yii::t('mc', 'Summon Role'),
            'chat_role' => Yii::t('mc', 'Chat Role'),
            'user_jar' => Yii::t('mc', 'Owner selectable JAR'),
            'user_ftp' => Yii::t('mc', 'Owner managed FTP'),
            'user_schedule' => Yii::t('mc', 'Owner Scheduled Tasks'),
            'visible' => Yii::t('mc', 'Server Visibility'),
            'user_name' => Yii::t('mc', 'Owner can set Name'),
            'user_visibility' => Yii::t('mc', 'Owner set Visibility'),
            'display_ip' => Yii::t('mc', 'Displayed IP'),
            'user_players' => Yii::t('mc', 'Owner set Player Count'),
            'user_mysql' => Yii::t('mc', 'Owner can create MySQL DB'),
        );
    }

    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('`server_id`',$this->server_id);
        $criteria->compare('`ip_auth_role`',$this->ip_auth_role);
        $criteria->compare('`give_role`',$this->give_role);
        $criteria->compare('`tp_role`',$this->tp_role);
        $criteria->compare('`summon_role`',$this->summon_role);
        $criteria->compare('`chat_role`',$this->chat_role);
        $criteria->compare('`user_jar`',$this->user_jar);
        $criteria->compare('`user_ftp`',$this->user_ftp);
        $criteria->compare('`user_schedule`',$this->user_schedule);
        $criteria->compare('`user_name`',$this->user_name);
        $criteria->compare('`user_visibility`',$this->user_visibility);
        $criteria->compare('`visible`',$this->visible);
        $criteria->compare('`display_ip`',$this->display_ip,true);
        $criteria->compare('`user_players`',$this->user_players);
        $criteria->compare('`user_mysql`',$this->user_mysql);

        return new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,
        ));
    }

    public function beforeSave()
    {
        if ($this->isNewRecord && !$this->display_ip)
            $this->display_ip = Yii::app()->params['default_display_ip'];
        return true;
    }
}
