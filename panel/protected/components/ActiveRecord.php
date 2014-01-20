<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/


class ActiveRecord extends CActiveRecord
{
    public function __get($name)
    {
        try
        {
            return parent::__get($name);
        }
        catch(Exception $e)
        {
            $error = $e->getMessage();
            $msg = 'Failed to retrieve a required property ("'.$name.'"): '.$error.'<br/>This can be due to an outdated database.<br/>';
            if (!(Yii::app()->controller instanceof InstallController))
            {
                $msg .= '<br/>Please run the '.CHtml::link('control panel installer',
                Yii::app()->request->getBaseUrl(true).'/install.php').' to fix this issue.<br/>';
            }
            throw new RawHttpException(500, $msg);
        }
    }
   
}
