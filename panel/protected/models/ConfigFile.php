<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class ConfigFile extends ActiveRecord
{
/*
    int $id
    string $name
    string $description
    string $file
    string $options
    string $type
    int $enabled
    string $dir
*/

    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'config_file';
    }

    public function rules()
    {
        return array(
            array('name, file', 'required'),
            array('name, description, file, options, type, enabled, dir', 'safe'),
            array('id, name, description, file, options, type, enabled, dir', 'safe', 'on'=>'search'),
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
            'description' => Yii::t('mc', 'Description'),
            'file' => Yii::t('mc', 'File'),
            'options' => Yii::t('mc', 'Options'),
            'type' => Yii::t('mc', 'Type'),
            'enabled' => Yii::t('mc', 'Enabled'),
            'dir' => Yii::t('mc', 'Directory'),
        );
    }

    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('`id`',$this->id);
        $criteria->compare('`name`',$this->name,true);
        $criteria->compare('`description`',$this->description,true);
        $criteria->compare('`file`',$this->file,true);
        $criteria->compare('`options`',$this->options,true);
        $criteria->compare('`type`',$this->type,true);
        $criteria->compare('`enabled`',$this->enabled,true);
        $criteria->compare('`dir`',$this->dir,true);

        return new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,
        ));
    }
}
