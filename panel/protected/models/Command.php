<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class Command extends ActiveRecord
{
/*
    int $id
    int $server_id
    string $name
    int $level
    int $prereq
    string $chat
    string $response
    string $run
    int $hidden
*/

    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'command';
    }

    public function getDbConnection()
    {
        return Yii::app()->bridgeDb;
    }

    public function rules()
    {
        return array(
            array('name', 'required'),
            array('server_id', 'numerical', 'on'=>'superuser'),
            array('name', 'checkName'),
            array('level', 'numerical', 'integerOnly'=>true),
            array('prereq, chat, response, run', 'safe'),
            array('id, server_id, name, level, prereq, chat, response, run, hidden', 'safe', 'on'=>'search'),
            array('name, level, prereq, chat, response, run', 'safe', 'on'=>'serverSearch'),
            array('hidden', 'safe', 'on'=>'superuser'),
            array('server_id', 'unsafe', 'on'=>'serverSearch'),
        );
    }

    public function checkName($attribute, $params)
    {
        $other = $this->findAllByAttributes(array('name'=>$this->name, 'server_id'=>$this->server_id));
        if (!count($other))
            return;
        if (count($other) > 1 || $other[0]->id != $this->id)
            $this->addError('name', Yii::t('mc', 'This name is already in use by another command.'));
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
            'level' => Yii::t('mc', 'Required Role'),
            'prereq' => Yii::t('mc', 'Prerequisite'),
            'chat' => Yii::t('mc', 'Chat'),
            'response' => Yii::t('mc', 'Response'),
            'run' => Yii::t('mc', 'Run'),
            'hidden' => Yii::t('mc', 'Hidden'),
        );
    }

    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('`id`',$this->id);
        $criteria->compare('`server_id`',$this->server_id);
        $criteria->compare('`name`',$this->name,true);
        $criteria->compare('`level`',$this->level);
        $criteria->compare('`prereq`',$this->prereq);
        $criteria->compare('`chat`',$this->chat,true);
        $criteria->compare('`response`',$this->response,true);
        $criteria->compare('`run`',$this->run,true);
        if ($this->hasAttribute('hidden'))
            $criteria->compare('`hidden`',$this->hidden,false);

        return new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,
            'pagination'=>array('pageSize'=>20),
        ));
    }

    public function afterSave()
    {
        if ($this->server_id)
            McBridge::get()->serverCmd($this->server_id, 'load command '.$this->id);
        return true;
    }

    public function afterDelete()
    {
        return $this->afterSave();
    }
}

