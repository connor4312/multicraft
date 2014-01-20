<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class StatusController extends Controller
{
    /**
     * BEGIN Banner settings
     */
    var $banner = 'default.png';
    var $font = 'LiberationSans-Regular.ttf';
    var $color = array(70, 70, 70);
    var $statusIcons = array('online.png', 'offline.png');

    var $titleSize = 14;
    var $ipSize = 12;
    var $statusSize = 12;

    var $textX = 78;
    var $statusOffset = 25; //spacing to status icon
    var $ipOffset = 3; //distance from right border
    var $titleY = 20;
    var $iconY = 30;
    var $statusY = 44;
    /**
     * END Banner settings
     */

    public function filters()
    {
        return array(
            'accessControl',
        );
    }

    public function accessRules()
    {
        return array(
            array('allow',
                'expression'=>'!!Yii::app()->params["status_banner"]',
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    public function getImg($path)
    {
        $is = getimagesize($path);
        $type = $is[2];
        $img = false;
        if ($type == 1)
            $img = imagecreatefromgif($path);
        else if ($type == 2)
            $img = imagecreatefromjpeg($path);
        else
            $img = imagecreatefrompng($path);
        if (!$img)
            throw new Exception('Failed to load image from "'.$path.'".');
        imagealphablending($img,true); 
        return $img;
    }

    public function createAction($actionID)
    {
        if (!isset($_GET['id']))
            $_GET['id'] = $actionID;
        return parent::createAction('index');
    }

    public function actionIndex($id)
    {
        $sv = Server::model()->findByPk((int)$id);

        if (!$sv)
            throw new Exception('Server not found');

        $cfg = ServerConfig::model()->findByPk((int)$id);

        $pl = $sv->getOnlinePlayers();

        $st = $pl >= 0 ? 'online' : 'offline';

        $image = $this->getImg(Theme::themeFilePath('images/status/'.$this->banner));
        $statusIcon = $this->getImg(Theme::themeFilePath('images/status/'.$this->statusIcons[$st == 'online' ? 0 : 1]));
        $font = Theme::themeFilePath('images/status/'.$this->font);
        $color = imagecolorallocate($image, $this->color[0], $this->color[1], $this->color[2]);

        //Status icon
        imagecopy($image, $statusIcon, $this->textX, $this->iconY, 0, 0, imagesx($statusIcon), imagesy($statusIcon));
        imagedestroy($statusIcon);

        //Server name
        imagettftext($image, $this->titleSize, 0, $this->textX, $this->titleY, $color, $font, $sv->name);
        //Server IP
        $ipStr = trim(($cfg && $cfg->display_ip) ? $cfg->display_ip : $sv->ip);
        if (!strlen($ipStr) || $ipStr == '0.0.0.0')
        {
            if ($dmn = Daemon::model()->findByPk($sv->daemon_id))
                $ipStr = $dmn->ip;
        }
        $ipStr = 'IP: '.$ipStr.':'.$sv->port;
        $sz = imagettfbbox($this->ipSize, 0, $font, $ipStr);
        imagettftext($image, $this->ipSize, 0, imagesx($image) - ($sz[2] - $sz[0]) - $this->ipOffset, $this->statusY, $color, $font,
            $ipStr);

        //Server status
        if ($st == 'online')
        {
            imagettftext($image, $this->statusSize, 0, $this->textX + $this->statusOffset, $this->statusY, $color, $font,
                $pl.' / '.$sv->players.' '.Yii::t('mc', 'Players'));
        }
        else
        {
            imagettftext($image, $this->statusSize, 0, $this->textX + $this->statusOffset, $this->statusY, $color, $font,
                Yii::t('mc', 'Offline'));
        }

        imagecolordeallocate($image, $color);

        header('Content-type: image/png');
        imagepng($image);
        imagedestroy($image);
    }
}
