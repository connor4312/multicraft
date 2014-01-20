<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
?>
<!doctype html>
<html lang="en">
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
    <meta charset="UTF-8" />

    <link rel="stylesheet" type="text/css" href="<?php echo Theme::css('style.css') ?>" media="screen, projection" />

    <title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>

<body>
<div id="page">
    <div class="container">
        <div class="row" id="header">
            <div id="logo">
                <h1><?php echo CHtml::encode(Yii::app()->name); ?><small><?php echo Yii::t('mc', 'Minecraft Server Manager') ?></small></h1>
            </div>
        </div><!-- header -->

        <nav class="navbar navbar-default navbar-static-top navbar-inverse" role="navigation" id="navbar">
            <?php
            $items = array();

            $simple = (Yii::app()->theme && in_array(Yii::app()->theme->name, array('simple', 'mobile', 'platform')));
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
                );
            }
            else
            {
                $items[] = array(
                    'label'=>Yii::t('mc', 'Logout ({name})', array('{name}'=>Yii::app()->user->name)),
                    'url'=>array('/site/logout'),
                );
            }
            $items[] = array(
                'label'=>Yii::t('mc', 'About'),
                'url'=>array('/site/page', 'view'=>'about'),
                'visible'=>$simple,
                'itemOptions'=>array('style'=>'float: right'),
            );
            
            
            $this->widget('zii.widgets.CMenu', array(
                'items'=>$items,
                'htmlOptions'=>array('class' => 'nav navbar-nav')
            ));
            ?>
        </nav>

        <div class="row" id="content"><?php echo $content; ?></div>

    </div>
</div>
<footer>
    Powered by <a href="http://www.multicraft.org">Multicraft Control Panel</a>
</footer>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="<?php echo Theme::js('bootstrap.js') ?>"></script>
<script src="<?php echo Theme::js('multicraft.js') ?>"></script>
</body>
<!--  C o p y r i g h t   (c)   2 0 1 0 - 2 0 1 2   b y   x h o s t . c h   G m b H .   A l l   r i g h t s   r e s e r v e d .  -->
</html>
