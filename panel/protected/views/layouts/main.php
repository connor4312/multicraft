<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<!--
 -
 -   Copyright © 2010-2012 by xhost.ch GmbH
 -
 -   All rights reserved.
 -
 -->
<head>
    <meta content="initial-scale=1.0, width=device-width, user-scalable=yes" name="viewport">
    <meta content="yes" name="apple-mobile-web-app-capable">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="language" content="en" />
    <link rev="made" href="mailto:multicraft@xhost.ch">
    <meta name="description" content="Multicraft: The Minecraft server control panel">
    <meta name="keywords" content="Multicraft, Minecraft, server, management, control panel, hosting">
    <meta name="author" content="xhost.ch GmbH">

    <!-- blueprint CSS framework -->
    <link rel="stylesheet" type="text/css" href="<?php echo Theme::css('screen.css') ?>" media="screen, projection" />
    <link rel="stylesheet" type="text/css" href="<?php echo Theme::css('print.css') ?>" media="print" />
    <!--[if lt IE 8]>
    <link rel="stylesheet" type="text/css" href="<?php echo Theme::css('ie.css') ?>" media="screen, projection" />
    <![endif]-->

    <link rel="stylesheet" type="text/css" href="<?php echo Theme::css('main.css') ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo Theme::css('form.css') ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo Theme::css('theme.css') ?>" />

    <title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>

<body>

<div id="frame">
<div id="border-left"></div>
<div id="border-right"></div>
<div class="container" id="page">

    <div id="header">
        <div id="logo">
            <div id="logo_icon"><?php echo Theme::img('multicraft.png') ?></div>
            <div id="logo_text"><?php echo Theme::img('logo.png') ?></div>
            <div id="header_text"> <?php echo CHtml::encode(Yii::app()->name); ?><br/><span style="font-size: 12px"><?php echo Yii::t('mc', 'Minecraft Server Manager') ?></span></div>
        </div>
    </div><!-- header -->

    <div id="mainmenu">
        <?php
        $items = array();

        $simple = (Yii::app()->theme && in_array(Yii::app()->theme->name, array('simple', 'mobile', 'platform')));
        if (!$simple)
            $items[] = array('label'=>Yii::t('mc', 'Home'), 'url'=>array('/site/page', 'view'=>'home'));
        if (@Yii::app()->params['installer'] !== 'show')
        {
            $items[] = array(
                'label'=>Yii::t('mc', 'Servers'),
                'url'=>array('/server/index', 'my'=>($simple && !Yii::app()->user->isSuperuser() ? 1 : 0)),
            );
            $items[] = array(
                'label'=>Yii::t('mc', 'Users'),
                'url'=>array('/user/index'),
                'visible'=>(Yii::app()->user->isSuperuser()
                    || !(Yii::app()->user->isGuest || (Yii::app()->params['hide_userlist'] === true) || $simple)),
            );
            $items[] = array(
                'label'=>Yii::t('mc', 'Profile'),
                'url'=>array('/user/view', 'id'=>Yii::app()->user->id),
                'visible'=>(!Yii::app()->user->isSuperuser() && !Yii::app()->user->isGuest
                    && ((Yii::app()->params['hide_userlist'] === true) || $simple)),
            );
            $items[] = array(
                'label'=>Yii::t('mc', 'Settings'),
                'url'=>array('/daemon/index'),
                'visible'=>Yii::app()->user->isSuperuser(),
            );
            $items[] = array(
                'label'=>Yii::t('mc', 'Support'),
                'url'=>array('/site/report'),
                'visible'=>!empty(Yii::app()->params['admin_email']),
            );
        }
        if (Yii::app()->user->isGuest)
        {
            $items[] = array(
                'label'=>Yii::t('mc', 'Login'),
                'url'=>array('/site/login'),
                'itemOptions'=>$simple ? array('style'=>'float: right') : array(),
            );
        }
        else
        {
            $items[] = array(
                'label'=>Yii::t('mc', 'Logout ({name})', array('{name}'=>Yii::app()->user->name)),
                'url'=>array('/site/logout'),
                'itemOptions'=>$simple ? array('style'=>'float: right') : array(),
            );
        }
        $items[] = array(
            'label'=>Yii::t('mc', 'About'),
            'url'=>array('/site/page', 'view'=>'about'),
            'visible'=>$simple,
            'itemOptions'=>array('style'=>'float: right'),
        );
        
        
        $this->widget('zii.widgets.CMenu',array('items'=>$items)); ?>
        <?php if (!$simple): ?>
            <div class="notice"><?php echo $this->notice ?></div>
        <?php endif ?>
    </div><!-- mainmenu -->

    <div id="outer">

    <?php
        if (!$simple)
        {
            array_pop($this->breadcrumbs);
            $this->widget('zii.widgets.CBreadcrumbs', array(
                'homeLink'=>'',
                'links'=>$this->breadcrumbs,
            ));
        }
    ?><!-- breadcrumbs -->

    <?php echo $content; ?>

    </div>
    <?php if (Yii::app()->params['copyright']): ?>
    <div id="footer">
        Powered by <a href="http://www.multicraft.org">Multicraft Control Panel</a>
    </div><!-- footer -->
    <?php endif ?>

</div><!-- page -->
</div>
</body>
<!--  C o p y r i g h t   (c)   2 0 1 0 - 2 0 1 2   b y   x h o s t . c h   G m b H .   A l l   r i g h t s   r e s e r v e d .  -->
</html>
