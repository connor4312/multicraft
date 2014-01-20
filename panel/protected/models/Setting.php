<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class Setting extends ActiveRecord
{
/*
    string $key
    string $value
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
        return 'setting';
    }
}
