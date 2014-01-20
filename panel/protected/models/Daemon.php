<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class Daemon extends ActiveRecord
{
/*
    int $id
    string $name
    string $ip
    int $port
    string $token
    int $memory
    string $ftp_ip
    int $ftp_port
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
        return 'daemon';
    }

    public function rules()
    {
        return array(
            array('id, name, token', 'required'),
            array('id, port', 'numerical', 'integerOnly'=>true),
            array('name, ip, token', 'length', 'max'=>128),
            array('id, name, ip, port, token', 'safe', 'on'=>'search'),
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
            'id' => Yii::t('mc', 'ID'),
            'name' => Yii::t('mc', 'Name'),
            'ip' => Yii::t('mc', 'IP'),
            'port' => Yii::t('mc', 'Port'),
            'token' => Yii::t('mc', 'Token'),
        );
    }

    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('`id`',$this->id);
        $criteria->compare('`name`',$this->name,true);
        $criteria->compare('`ip`',$this->ip,true);
        $criteria->compare('`port`',$this->port);
        $criteria->compare('`token`',$this->token,true);

        return new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,
        ));
    }

    public function getUsedMemory($includeSuspended = false)
    {
        $sql = 'select sum(`memory`) from `server` where `daemon_id`=?';
        if (!$includeSuspended)
            $sql .= ' and `suspended`=0';
        $cmd = $this->dbConnection->createCommand($sql);
        $cmd->bindValue(1, (int)$this->id);
        $used = $cmd->queryScalar();
        return ($used ? $used : 0);
    }
}
