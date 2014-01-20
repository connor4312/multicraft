<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class DbConnection extends CDbConnection
{
    public $version = 0;
    public $type = '';

    public function init()
    {
        parent::init();
    }

    public function checkNotice()
    {
        if ($this->type && $this->version && ($this->getV() < $this->version))
        {
            Yii::app()->user->setFlash('superuser_notice',
                'The '.$this->type.' database is not up to date, please run the '
                .CHtml::link('control panel installer', Yii::app()->request->getBaseUrl(true).'/install.php'));
        }
    }

    protected function open()
    {
        try
        {
            parent::open();
        }
        catch(Exception $e)
        {
            $error = $e->getMessage();
            $msg = 'Failed to connect to the '.$this->type.' database.<br/>';
            $msg .= 'The error message is: '.$error.'<br/>';
            if (!(Yii::app()->controller instanceof InstallController))
            {
                $msg .= '<br/>Please run the '.CHtml::link('control panel installer',
                Yii::app()->request->getBaseUrl(true).'/install.php').' to fix this issue.<br/>';
            }
            throw new RawHttpException(500, $msg);
        }
    }

    
    public function isSqlite()
    {
        return preg_match('/^sqlite:/', $this->connectionString);
    }

    public function getV()
    {
        if ($this->type == 'daemon')
            $sql = 'select `value` from `setting` where `key`=\'dbVersion\'';
        else
            $sql = 'select `version` from `version` where `id`=1';
        $cmd = $this->createCommand($sql);
        $v = 0;
        try
        {
            $v = (int)$cmd->queryScalar();
        }
        catch(Exception $e) {}
        return $v;
    }

    public function executeFile($fileName)
    {
        if (!@is_readable($fileName))
            throw new RawHttpException(500, 'File not found or not readable: '.$fileName);
        $file = @fopen($fileName, 'r');
        if (!$file)
            throw new RawHttpException(500, 'Failed to open file: '.$fileName);
        $sql = array();
        while (!feof($file))
        {
            $sql[] = fgets($file);

            if (preg_match('/;\s*$/', end($sql)))
            {
                $sql = trim(implode('', $sql));
                if (!strlen($sql))
                    continue;
                $cmd = $this->createCommand($sql);
                $cmd->execute();
                $sql = array();
            }
        }
    }

    public function dbCheck($ignoreErrors = false)
    {
        if (!$this->type || !$this->version)
            return;

        if ($this->isSqlite())
        {
            $file = substr($this->connectionString, strlen('sqlite:'));
            if ((!file_exists($file) || !filesize($file)) && file_exists($file.'.dist'))
                copy($file.'.dist', $file);                
        }

        $v = $this->getV();

        $driver = $this->driverName;
        while ($v < $this->version)
        {
            $v++;
            $fileName = dirname(__FILE__).'/../data/'.$this->type.'/update.'.$driver.'.'.$v.'.sql';
            try
            {
                $this->executeFile($fileName);
            }
            catch(Exception $e)
            {
                if (!$ignoreErrors)
                    throw new RawHttpException(500, $this->updateError($v).'Update query failed: '.$e->getMessage());
            }
            try
            {
                if ($this->type == 'daemon')
                    $cmd = $this->createCommand('replace into `setting` (`key`, `value`) values(\'dbVersion\',?)');
                else
                    $cmd = $this->createCommand('replace into `version` (`id`, `version`) values(1,?)');
                $cmd->bindParam(1, $v);
                $cmd->execute();
            }
            catch(Exception $e)
            {
                throw new RawHttpException(500, $this->updateError($v).'Setting database version failed: '.$e->getMessage());
            }
        }
    }

    private function updateError($v)
    {
        if ($this->type == 'daemon')
            $entry = 'the value of the row with key "dbVersion" in the "setting" table';
        else
            $entry = 'the entry in the "version" table';
        return 'Failed to update '.$this->type.' database from version '.($v-1).' to '.$v.' (latest: '.$this->version.').<br/>'
            .' If you believe that this update has already been applied or if you applied it manually'
            .' using an "update.*.*.sql" file, please change '.$entry.' to '.$v.'.<br/><br/>'
            .' Detailed error message:<br/>';
    }
}
