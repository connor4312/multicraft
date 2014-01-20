<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class Player extends ActiveRecord
{
/*
    int $id
    string $server_id
    string $name
    int $level
    string $lastseen
    string $banned
    string $op
    string $status
    string $ip
    string $previps
    string $quitreason
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
        return 'player';
    }

    public function rules()
    {
        return array(
            array('name', 'required'),
            array('name', 'checkName'),
            array('name', 'filter', 'filter' => 'trim'),
            array('level', 'numerical', 'integerOnly'=>true),
            array('name, level, banned', 'safe'),
            array('server_id', 'safe', 'on'=>'superuser'),
            array('id, server_id, name, level, lastseen, banned, op, ip', 'safe', 'on'=>'search'),
            array('name, level, lastseen, banned, op, ip', 'safe', 'on'=>'serverSearch'),
            array('server_id', 'unsafe', 'on'=>'serverSearch'),
        );
    }

    public function checkName($attribute, $params)
    {
        $other = $this->findAllByAttributes(array('name'=>$this->name, 'server_id'=>$this->server_id));
        if (!count($other))
            return;
        if (count($other) > 1 || $other[0]->id != $this->id)
            $this->addError('name', Yii::t('mc', 'This name is already in use by another player.'));
    }

    public function relations()
    {
        return array(
            'server' => array(self::BELONGS_TO, 'server', 'server_id'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'id' => Yii::t('mc', 'ID'),
            'server_id' => Yii::t('mc', 'Server'),
            'name' => Yii::t('mc', 'Name'),
            'level' => Yii::t('mc', 'Role'),
            'lastseen' => Yii::t('mc', 'Last Seen'),
            'banned' => Yii::t('mc', 'Banned'),
            'op' => Yii::t('mc', 'Op'),
            'status' => Yii::t('mc', 'Status'),
            'ip' => Yii::t('mc', 'IP'),
            'previps' => Yii::t('mc', 'Previous IPs'),
            'quitreason' => Yii::t('mc', 'Last Quit Reason'),
        );
    }

    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('`id`',$this->id);
        $criteria->compare('`server_id`',$this->server_id);
        $criteria->compare('`name`',$this->name,true);
        $criteria->compare('`level`',$this->level);
        $criteria->compare('`lastseen`',$this->lastseen,true);
        $criteria->compare('`banned`',$this->banned,true);
        $criteria->compare('`op`',$this->op,true);
        $criteria->compare('`status`',$this->status,true);
        $criteria->compare('`ip`',$this->ip,true);
        $criteria->compare('`previps`',$this->previps,true);
        $criteria->compare('`quitreason`',$this->quitreason,true);

        return new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,
            'pagination'=>array('pageSize'=>20),
        ));
    }

    public function getUser()
    {
        $sql = 'select `user_id` from `user_player` where `player_id`=?';
        $cmd = Yii::app()->db->createCommand($sql);
        $cmd->bindValue(1, $this->id);
        return $cmd->queryScalar();
    }

    public function setUser($usr, $save = true)
    {
        if (!is_object($usr))
            $usr = User::model()->findByPk((int)$usr);
        $sql = 'replace into `user_player` (`user_id`,`player_id`) values(?,?)';
        $cmd = Yii::app()->db->createCommand($sql);
        $cmd->bindValue(1, $usr ? $usr->id : 0);
        $cmd->bindValue(2, $this->id);
        if (!$cmd->execute())
            return false;
        if ($usr)
            $this->level = $usr->getLevel($this->server_id);
        else
            $this->level = 1;
        if (!$save)
            return true;
        return $this->save();
    }

    public function afterSave()
    {
        McBridge::get()->serverCmd($this->server_id, 'load player '.$this->id);
        return true;
    }

    public function afterDelete()
    {
        UserPlayer::model()->deleteAllByAttributes(array('player_id'=>$this->id));
        return $this->afterSave();
    }
}
