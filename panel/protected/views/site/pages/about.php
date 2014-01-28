<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle=Yii::app()->name . ' - '.Yii::t('mc', 'About');
$this->breadcrumbs=array(
    Yii::t('mc', 'About'),
);

$this->menu=array(
    array(
        'label'=>Yii::t('mc', 'Back'),
        'url'=>array('', 'view'=>'home'),
        'icon'=>'arrow-left',
    ),
);
?>
<h2><?php echo Yii::t('mc', 'About Multicraft') ?></h2>
<div class="row">
   <div class="col-lg-8 muted"> <?php echo 'Copyright &copy; 2010-2012 by '.CHtml::link('xhost.ch GmbH', 'http://www.xhost.ch/').'. All Rights Reserved.'; ?></div>
    <?php echo Theme::img('about/swissmade.png', '', array('class' => 'col-lg-4')) ?>
</div>

<br/>
<?php echo Yii::t('mc', 'Multicraft uses the following technologies:') ?><br/>
<div id="about">
    <div class="row">
        <?php echo CHtml::link(Theme::img('about/python.png'), 'http://www.python.org', array('class' => 'col-md-6')); ?>
        <?php echo CHtml::link(Theme::img('about/pyftpdlib.png'), 'http://code.google.com/p/pyftpdlib/', array('class' => 'col-md-6')) ?>
    </div>
    <div class="row">
        <?php echo CHtml::link(Theme::img('about/psutil.png'), 'http://code.google.com/p/psutil/', array('class' => 'col-md-6')) ?>
        <?php echo CHtml::link(Theme::img('about/php.png'), 'http://www.php.net', array('class' => 'col-md-6')); ?>
    </div>
    <div class="row">
        <?php echo CHtml::link(Theme::img('about/yii.png'), 'http://www.yiiframework.com', array('class' => 'col-md-6')); ?>
        <?php echo CHtml::link(Theme::img('about/net2ftp.png'), 'http://www.net2ftp.com/', array('class' => 'col-md-6')); ?>
    </div>
    <div class="row">
        <?php echo CHtml::link(Theme::img('about/lesscss.png'), 'http://lesscss.org/', array('class' => 'col-md-6')); ?>
        <?php echo CHtml::link(Theme::img('about/gruntjs.png'), 'http://gruntjs.com/', array('class' => 'col-md-6')); ?>
    </div>
    <div class="row">
        <?php echo CHtml::link(Theme::img('about/nodejs.png'), 'http://nodejs.org/', array('class' => 'col-md-6')); ?>
        <?php echo CHtml::link(Theme::img('about/bootstrap.png'), 'http://getbootstrap.com/', array('class' => 'col-md-6')); ?>
    </div>
    <div class="row">
        <?php echo CHtml::link(Theme::img('about/xhost.png'), 'http://www.xhost.ch/', array('class' => 'col-md-12')); ?>
        <?php echo CHtml::link(Theme::img('about/multicraft.png'), 'http://www.multicraft.org/', array('class' => 'col-md-12')); ?>
    </div>
</div>