<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class Theme extends CTheme
{
    private $_vpath = '';

    static function slash(&$file)
    {
        if (strlen($file) && $file[0] != '/')
            $file = '/'.$file;
        return $file;
    }

    static function themeFilePath($file)
    {
        Theme::slash($file);
        if (Yii::app()->theme && file_exists(Yii::app()->theme->basePath.$file))
            return Yii::app()->theme->basePath.$file;
        return Yii::getPathOfAlias('webroot').'/'.$file;
    }

    static function themeFile($file)
    {
        Theme::slash($file);
        if (Yii::app()->theme && file_exists(Yii::app()->theme->basePath.$file))
            return Yii::app()->theme->baseUrl.$file;
        return Yii::app()->baseUrl.$file;
    }

    static function css($file)
    {
        Theme::slash($file);
        return Theme::themeFile('css'.$file);
    }

    static function img($file, $alt = '', $htmlOptions = array())
    {
        Theme::slash($file);
        return CHtml::image(Theme::themeFile('images'.$file), $alt, $htmlOptions);
    }

    public function getViewPath()
    {
        if ($this->_vpath)
            return $this->_vpath;
        if (preg_match('/platform'.preg_quote(DIRECTORY_SEPARATOR, '/').'/', $this->name))
            $this->_vpath = Yii::app()->basePath.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$this->name;
        else
            $this->_vpath = CTheme::getViewPath();
        return $this->_vpath;
    }
}
