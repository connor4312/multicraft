<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class Schedule extends ActiveRecord
{
/*
    int $id
    int $server_id
    int $scheduled_ts
    int $last_run_ts
    int $interval
    string $name
    int $command
    int $run_for
    int $status
    string $args
    int $hidden
*/

    const Scheduled     = 0;
    const ReScheduled   = 1;
    const Done          = 2;
    const Paused        = 3;
    const Expired       = 4;
    const Failed        = 5;
    const Suspended     = 6;

    static function getStatusValues($idx = false)
    {
        static $sv = false;
        if (!$sv)
        {
            $sv = array(
                Schedule::Scheduled     => Yii::t('mc', 'Scheduled'),
                Schedule::ReScheduled   => Yii::t('mc', 'Re-Scheduled'),
                Schedule::Done          => Yii::t('mc', 'Done'),
                Schedule::Paused        => Yii::t('mc', 'Paused'),
                Schedule::Expired       => Yii::t('mc', 'Expired'),
                Schedule::Failed        => Yii::t('mc', 'Failed'),
                Schedule::Suspended     => Yii::t('mc', 'Suspended'),
            );
        }
        if ($idx !== false)
            return @$sv[$idx];
        return $sv;
    }

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
        return 'schedule';
    }

    public function rules()
    {
        return array(
            array('name, scheduled_ts, command', 'required'),
            array('name', 'checkName'),
            array('scheduled_ts, interval, status, command', 'numerical', 'integerOnly'=>true),
            array('name, run_for, command, status, args', 'safe'),
            array('hidden', 'safe', 'on'=>'superuser'),
            array('server_id', 'numerical', 'on'=>'superuser'),
            array('id, server_id, scheduled_ts, last_run_ts, interval, status, command, run_for, hidden', 'safe', 'on'=>'search'),
        );
    }

    public function checkName($attribute, $params)
    {
        $other = $this->findAllByAttributes(array('name'=>$this->name, 'server_id'=>$this->server_id));
        if (!count($other))
            return;
        if (count($other) > 1 || $other[0]->id != $this->id)
            $this->addError('name', Yii::t('mc', 'This name is already in use by another task.'));
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
            'server_id' => Yii::t('mc', 'Server ID'),
            'name' => Yii::t('mc', 'Name'),
            'scheduled_ts' => Yii::t('mc', 'Scheduled Time'),
            'last_run_ts' => Yii::t('mc', 'Last Run'),
            'interval' => Yii::t('mc', 'Interval'),
            'command' => Yii::t('mc', 'Command'),
            'run_for' => Yii::t('mc', 'Run For'),
            'status' => Yii::t('mc', 'Status'),
            'args' => Yii::t('mc', 'Arguments'),
            'hidden' => Yii::t('mc', 'Hidden'),
        );
    }

    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('`id`',$this->id);
        $criteria->compare('`server_id`',$this->server_id);
        $criteria->compare('`name`',$this->name,true);
        $criteria->compare('`scheduled_ts`',$this->scheduled_ts);
        $criteria->compare('`last_run_ts`',$this->last_run_ts);
        $criteria->compare('`interval`',$this->interval);
        $criteria->compare('`command`',$this->command,true);
        $criteria->compare('`run_for`',$this->run_for,true);
        $criteria->compare('`status`',$this->status,true);
        $criteria->compare('`args`',$this->args,true);
        $criteria->compare('`hidden`',$this->hidden,false);

        return new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,
            'pagination'=>array('pageSize'=>20),
        ));
    }

    public function getIntervalString()
    {
        if (!$this->interval)
            return Yii::t('mc', 'Run Once');
        if (!($this->interval % (24 * 3600)))
        {
            $amt = $this->interval / (24 * 3600);
            $str = ' '.Yii::t('mc', 'day|days', $amt);
        }
        else if (!($this->interval % 3600))
        {
            $amt = $this->interval / 3600;
            $str = ' '.Yii::t('mc', 'hour|hours', $amt);
        }
        else
        {
            $amt = $this->interval / 60;
            $str = ' '.Yii::t('mc', 'minute|minutes', $amt);
        }
        return $amt.$str;
    }
}
