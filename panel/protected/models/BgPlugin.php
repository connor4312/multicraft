<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class BgPlugin extends ActiveRecord
{
    // Configurable parameters

    static $apiUrl = 'http://api.bukget.org/api2';
    static $pluginsRefreshTime = 3600;
    static $categoriesRefreshTime = 3700;

    // End Configurable parameters


    static $con = null;
    private $_info = null;
    private $_cached = false;

    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function getDbConnection()
    {
        if (!BgPlugin::$con)
        {
            BgPlugin::$con = new CDbConnection('sqlite:'.dirname(__FILE__).'/../runtime/bukget.sqlite');
            BgPlugin::$con->createCommand('create table if not exists `updated`'
                .' (`name` text primary key not null, `time` integer not null)')->execute();
            BgPlugin::$con->createCommand('create table if not exists `category_plugin`'
                .' (`category` text, `plugin` text, unique(`category`,`plugin`))')->execute();
            BgPlugin::$con->createCommand('create table if not exists `category` (`name` text, unique(`name`))')->execute();
            BgPlugin::$con->createCommand('create table if not exists `plugin`'
                .' (`name` text primary key, `plugin_name` text, `status` text, `link` text,'
                .' `desc` text, `categories` text, unique(`name`))')->execute();
        }
        return BgPlugin::$con;
    }

    public function tableName()
    {
        return 'plugin';
    }

    public function rules()
    {
        return array(
            array('name, plugin_name, categories, authors, status, link, desc', 'safe', 'on'=>'search'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'name' => Yii::t('mc', 'Name'),
            'plugin_name' => Yii::t('mc', 'Plugin Name'),
            'categories' => Yii::t('mc', 'Categories'),
            'authors' => Yii::t('mc', 'Authors'),
            'status' => Yii::t('mc', 'Status'),
            'link' => Yii::t('mc', 'Plugin Page'),
            'desc' => Yii::t('mc', 'Description'),
        );
    }

    public function search($server_id = 0)
    {
        $criteria=new CDbCriteria;

        $criteria->compare('`name`',$this->name,true);
        $criteria->compare('`plugin_name`',$this->plugin_name,true);
        $criteria->compare('`status`',$this->status,true);
        $criteria->compare('`link`',$this->link,true);
        $criteria->compare('`desc`',$this->desc,true);

        if ($this->categories)
        {
            $cmd = $this->getDbConnection()->createCommand('select `name` from `category` where `name` like ?');
            $cat = $cmd->queryScalar(array($this->categories));
            if ($cat)
            {
                $cmd = $this->getDbConnection()->createCommand('select count(*) from `category_plugin` where `category`=? limit 1');
                if (!$cmd->queryScalar(array($cat)))
                {
                    Yii::log('BukGet: Updating category '.$cat);
                    $trans = $this->getDbConnection()->beginTransaction();
                    $cmd = $this->getDbConnection()->createCommand('insert into `category_plugin` (`category`,`plugin`) values(?,?)');

                    $ps = CJSON::decode(file_get_contents(BgPlugin::$apiUrl.'/bukkit/category/'.
                        CHtml::encode($cat).'?fields=name'), false);
                    foreach ($ps as $p)
                    {
                        if (!@$p->name)
                            continue;
                        $cmd->execute(array($cat, $p->name));
                    }

                    $trans->commit();
                    Yii::log('BukGet: Done updating category');
                }

                $criteria->addCondition('`name` in (select `plugin` from `category_plugin` where `category`='
                    .$this->getDbConnection()->quoteValue($cat).')');
            }
        }

        if ($server_id)
        {
            $c = Yii::app()->bridgeDb->createCommand('select `name` from `bgplugin` where `server_id`=? and `version`!=\'\'');
            $c->bindValue(1, $server_id);
            $names = $c->queryColumn();
            $criteria->addInCondition('`name`', $names);
        }

        return new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,
            'pagination'=>array('pageSize'=>20),
        ));
    }

    private function getCache()
    {
        if ($this->_cached)
            return $this->_cached;
        return ($this->_cached = CJSON::decode(file_get_contents(BgPlugin::$apiUrl.'/bukkit/plugin/'.CHtml::encode($this->name)), false));
    }

    public function checkPlugins()
    {
        $count = $this->getDbConnection()->createCommand('select `name` from `plugin` limit 1');
        $time = $this->getDbConnection()->createCommand('select `time` from `updated` where `name`=\'plugins\'');
        if (!$count->queryScalar() || ($time->queryScalar() + BgPlugin::$pluginsRefreshTime) < time())
        {
            $list = CJSON::decode(file_get_contents(BgPlugin::$apiUrl.'/bukkit/plugins?fields=name,plugname,stage,link,description'), false);

            Yii::log('BukGet: Inserting '.count($list).' plugins');
            $trans = $this->getDbConnection()->beginTransaction();
            $this->getDbConnection()->createCommand('delete from `plugin`')->execute();
            $cmd = $this->getDbConnection()->createCommand('insert into `plugin` (`name`,`plugin_name`,`status`,`link`,`desc`)'
                .' values(?,?,?,?,?)');
            foreach ($list as $p)
            {
                if (!@$p->name)
                    continue;
                $cmd->execute(array($p->name, @$p->plugname ? $p->plugname : $p->name, @$p->stage, @$p->link, @$p->description));
            }
            $cmd = $this->getDbConnection()->createCommand('replace into `updated` (`name`,`time`) values(?,?)');
            $cmd->execute(array('plugins', time()));
            $trans->commit();
            Yii::log('BukGet: Done inserting plugins');
        }
    }

    public function getAllCategories()
    {
        $cmd = $this->getDbConnection()->createCommand('select `name` from `category`');
        $cats = $cmd->queryColumn();
        $time = $this->getDbConnection()->createCommand('select `time` from `updated` where `name`=\'categories\'');
        if (!is_array($cats) || !count($cats) || ($time->queryScalar() + BgPlugin::$categoriesRefreshTime) < time())
        {
            $cats = CJSON::decode(file_get_contents(BgPlugin::$apiUrl.'/categories'), false);

            $pCats = array();

            Yii::log('BukGet: Building category information');
            $trans = $this->getDbConnection()->beginTransaction();
            $this->getDbConnection()->createCommand('delete from `category`')->execute();
            $this->getDbConnection()->createCommand('delete from `category_plugin`')->execute();
            $cmd = $this->getDbConnection()->createCommand('insert into `category` (`name`) values(?)');

            foreach ($cats as $c)
            {
                if (!strlen($c))
                    continue;
                $cmd->execute(array($c));
            }

            Yii::log('BukGet: Added '.count($cats).' categories');
            $cmd = $this->getDbConnection()->createCommand('replace into `updated` (`name`,`time`) values(?,?)');
            $cmd->execute(array('categories', time()));
            $trans->commit();
            Yii::log('BukGet: Done building category information');
        }
        return $cats;
    }

    public function getVersion()
    {
        $c = $this->getCache();
        $v = @$c->versions;
        if (!is_array($v) || empty($v))
            return '';
        return @$v[0]->version;
    }


    public function getDownloadLink()
    {
        $c = $this->getCache();
        $v = @$c->versions;
        if (!is_array($v) || empty($v))
            return '';
        return @$v[0]->download;
    }

    public function getAuthors()
    {
        $c = $this->getCache();
        return implode(@$c->authors, ', ');
    }
}
