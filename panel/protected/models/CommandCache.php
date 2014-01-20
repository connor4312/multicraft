<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class CommandCache extends CModel
{
    static $db = false;
    static function getDbConnection()
    {
        if (!CommandCache::$db)
        {
            if (Yii::app()->params['sqlitecache_commands'])
            {
                $file = realpath(dirname(__FILE__).'/../data/').'/cmdcache.db';
                $init = false;
                if (@!filesize($file))
                    $init = true;
                $db = new CDbConnection('sqlite:'.$file);
                $db->emulatePrepare = true;
                $db->charset = 'utf8';
                $db->schemaCachingDuration = '3600';
                $db->active = true;
                if ($init)
                {
                    $cmd = $db->createCommand('create table if not exists `command_cache` ('
                        .'`server_id` integer not null, `command` integer not null, `ts` integer not null,'
                        .' `data` text not null, primary key (`server_id`, `command`))');
                    $cmd->execute();
                }
                CommandCache::$db = $db;
            }
            else
                CommandCache::$db = Yii::app()->db;
        }
        return CommandCache::$db;
    }

    public function attributeNames()
    {
        return array();
    }

    static $cacheTime = array(
        'get status' => 3,
    );

    public static function model($className=__CLASS__)
    {
        return $className;
    }

    public function tableName()
    {
        return 'command_cache';
    }

    static function get($server, $cmd, &$data)
    {
        if (!($t = @CommandCache::$cacheTime[$cmd]))
            return 0;
        $ts1 = microtime(true);
        $ts2 = $ts1 - $t;
        $crc = crc32($cmd);
        $sql = 'select `data` as data from `command_cache` where `server_id`=? and `command`=?'
        .' and `ts`<=? and `ts`>=?';
        $cmd = CommandCache::getDbConnection()->createCommand($sql);
        $cmd->bindValue(1, (int)$server);
        $cmd->bindValue(2, $crc);
        $cmd->bindValue(3, $ts1);
        $cmd->bindValue(4, $ts2);
        $row = $cmd->queryRow();
        if (!$row)
            return -1;
        $data = unserialize($row['data']);
        if (!$data)
            return -1;
        return 1;
    }

    static function set($server, $cmd, $data)
    {
        $data = serialize($data);
        if (!$data)
            return;
        $ts = microtime(true);
        $crc = crc32($cmd);
        $db = CommandCache::getDbConnection();
        $sql = 'replace into `command_cache` (`server_id`, `command`, `ts`, `data`) values (?,?,?,?)';
        $cmd = $db->createCommand($sql);
        $cmd->bindValue(1, (int)$server);
        $cmd->bindValue(2, $crc);
        $cmd->bindValue(3, $ts);
        $cmd->bindValue(4, $data);
        $row = $cmd->execute();
    }

    static function clear()
    {
        $cmd = CommandCache::getDbConnection()->createCommand('delete from `command_cache`');
        return $cmd->execute();
    }
}
