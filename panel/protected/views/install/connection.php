<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle=Yii::app()->name . ' - '.Yii::t('mc', 'Daemon Connection');

?>
<h2>Daemon configuration</h2>
You can now configure and start your Multicraft daemon. Please verify these settings in your <b>multicraft.conf</b>:
<br/>
<div class="code"><?php
if ($p['daemon_db_driver'] == 'sqlite' && function_exists('posix_geteuid') && function_exists('posix_getpwuid'))
{
    $uid = posix_geteuid();
    $info = posix_getpwuid($uid);
    $name = @$info['name'];
    if (!@strlen($name))
        $name = $uid;
    if (!$name)
        $name = 'www-data';
    echo 'webUser = '.$name."\n";
}
else
{
    echo "#webUser = \n";
}

echo 'password = '.$p['config']['daemon_password']."\n";
echo 'database = '.$p['config']['daemon_db']."\n";
if ($p['daemon_db_driver'] == 'mysql')
{
    echo 'dbUser = '.$p['config']['daemon_db_user']."\n";
    echo 'dbPassword = '.$p['config']['daemon_db_pass']."\n";
}
?></div>
The default <b>start</b> command for Linux is:
<div class="code">/home/minecraft/multicraft/bin/multicraft -v start</div>
For Windows running the multicraft.exe is sufficient.<br/>
<br/>
<br/>
<h2>Detected Daemons</h2>
<?php if ($this->p['daemons']): ?>
One or more daemons have been detected. If you see at least one green box below you can complete the installation.
<?php echo CHtml::beginForm(array('index', 'step'=>'done')) ?>
<?php echo CHtml::submitButton('Continue') ?>
<?php echo CHtml::endForm() ?>
<?php else: ?>
No daemon has been detected in the database. Make sure that your daemon is using the correct database and that it starts up correctly (you can replace -v with -nv to debug startup issues).<br/>
<br/>
As soon as you see at least one green box below you can complete the installation.
<?php echo CHtml::beginForm(array('index', 'step'=>'connection')) ?>
<?php echo CHtml::submitButton('Refresh') ?>
<?php echo CHtml::endForm() ?>
<?php endif ?>
<br/>
Otherwise please refer to the <?php echo CHtml::link('Troubleshooting Guide', 'http://www.multicraft.org/site/page?view=troubleshooting') ?>.
<?php 
$title = $this->pageTitle;
$menu = $this->menu;
$breadcrumbs = $this->breadcrumbs;

$this->renderPartial('_daemonStatus',array(
    'daemonList'=>new CActiveDataProvider('Daemon', array(
        'criteria'=>array(
            'order'=>'id ASC',
        ),
        'pagination'=>array(
            'pageSize'=>5
        ),
    )),
));

$this->pageTitle = $title;
$this->menu = $menu;
$this->breadcrumbs = $breadcrumbs;

?>

