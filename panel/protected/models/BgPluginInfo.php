<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class BgPluginInfo extends ActiveRecord
{
    public $plugin = null;

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
        return 'bgplugin';
    }

    public function rules()
    {
        return array(
            array('id, name, plugin_name, categories, authors, status, link, desc', 'safe', 'on'=>'search'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'name' => Yii::t('mc', 'Name'),
            'version' => Yii::t('mc', 'Installed Version'),
            'installed_ts' => Yii::t('mc', 'Installation Time'),
            'installed_files' => Yii::t('mc', 'Installed Files'),
            'disabled' => Yii::t('mc', 'Status'),
        );
    }

    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('`name`',$this->name,true);
        $criteria->compare('`version`',$this->version,true);
        $criteria->compare('`installed_ts`',$this->installed_ts,true);
        $criteria->compare('`installed_files`',$this->installed_files,true);
        $criteria->compare('`disabled`',$this->disabled,true);

        return new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,
            'pagination'=>array('pageSize'=>20),
        ));
    }

    public function getInstalled()
    {
        if (!$this->plugin)
            return '';
        if ($this->version && $this->version == str_replace(':', ';', $this->plugin->getVersion()))
            return 'installed';
        else if ($this->version)
            return 'outdated';
        else
            return '';
    }
}
