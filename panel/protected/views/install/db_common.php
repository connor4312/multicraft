<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle=Yii::app()->name . ' - '.Yii::t('mc', 'Multicraft Installer');

?>
<?php if (!$p['success']): ?>
    <?php if (!@$p[$type.'_db_connected']): ?>
        Failed to connect to the <?php echo $type ?> database, please check that the database exists and the login data is correct.<br/>
        <br/>
        The error message above should give you some more information about why the connection failed. In case you're using SQLite please make sure the webserver user has write access to both the database file as well as the containing directory.<br/>
        <br/>
        <?php if (!@$p[$type.'_db_pdo']): ?>
            The PDO driver for the database type you have chosen is not installed. Please install this driver or select a different database type.
        <?php endif ?>
    <?php elseif (!@$p[$type.'_db_initialized']): ?>
        The <?php echo $type ?> database has not yet been initialized. You can let the installer initialize it with the schema from "protected/data/<?php echo $type ?>/schema.<?php echo $p[$type.'_db_driver'] ?>.sql":<br/>
        <br/>
        <?php 
        echo CHtml::beginForm(array('index', 'step'=>$type));
        echo CHtml::hiddenField('submit_init_'.$type.'_db', 'true');
        echo CHtml::submitButton('Initialize Database');
        echo CHtml::endForm(); ?>
    <?php elseif (!Yii::app()->user->isSuperuser()): ?>
        The <?php echo $type ?> database connection was successful and the users table contains data. Please log in to the panel using the "Login" button above to continue. The default username is <b>admin</b>, the default password is <b>admin</b>.<br/>
        Once you are logged in you can continue with the installation.<br/>
        <?php Yii::app()->user->returnUrl = array('install', 'step'=>$type) ?>
    <?php else: ?>
        There was an error during the database setup, please see the installer messages above for details.
        <?php 
        echo CHtml::beginForm(array('index', 'step'=>$type));
        echo CHtml::hiddenField('submit_ignore_error_'.$type.'_db', 'true');
        echo CHtml::submitButton('Ignore error (not recommended)', array('confirm'=>'WARNING: This will tell Multicraft that the database should be considered up to date even if this is not the case. If you do this with an outdated database undefined behavior might occur, use this option at your own risk!'));
        echo CHtml::endForm(); ?>
    <?php endif ?>
<?php elseif ($type == 'panel' && @$p['pw_issue']): ?>
    There seems to be an issue with the way passwords are stored in the "user" table of your panel database.<br/>
    <br/>
    <?php 
    echo CHtml::beginForm(array('index', 'step'=>($type == 'panel' ? 'daemon' : 'settings')));
    echo CHtml::submitButton('Ignore error (not recommended)', array('confirm'=>'WARNING: Having the passwords stored in a different way is a security risk and can be an indication of unwanted modifications to your panel files.'));
    echo CHtml::endForm(); ?>
<?php else: ?>
    The <?php echo $type ?> database setup was successful.<br/>
<br/>
    <?php echo CHtml::beginForm(array('index', 'step'=>($type == 'panel' ? 'daemon' : 'settings')));
    echo CHtml::submitButton('Continue');
    echo CHtml::endForm(); ?>
<?php endif ?>
<br/>
<br/>
<?php 
if (@$p[$type.'_db_initialized'] && !Yii::app()->user->isSuperuser())
    return;
if ($type == 'daemon')
    echo 'You will have to put the same information in your "multicraft.conf":';
echo CHtml::beginForm(array('index', 'step'=>$type));
echo CHtml::hiddenField('submit_'.$type.'_db', 'true');
$attr = array();

$attr[] = array('label'=>'Database Type', 'type'=>'raw', 'value'=>CHtml::dropDownList('driver', @$p[$type.'_db_driver'], array('sqlite'=>'SQLite', 'mysql'=>'MySQL', 'manual'=>'Manual')));

$attr[] = array('label'=>'DSN', 'cssClass'=>'manual', 'type'=>'raw', 'value'=>CHtml::textField('dbString', @$p['config'][$type.'_db']));
$attr[] = array('label'=>'', 'cssClass'=>'manual', 'type'=>'raw', 'value'=>'');
$attr[] = array('label'=>'Database Path', 'cssClass'=>'sqlite', 'type'=>'raw', 'value'=>CHtml::textField('dbPath', $p[$type.'_db_path']));
$attr[] = array('label'=>'', 'cssClass'=>'sqlite', 'type'=>'raw', 'value'=>'');
$attr[] = array('label'=>'Database Host', 'cssClass'=>'mysql', 'type'=>'raw', 'value'=>CHtml::textField('dbHost', $p[$type.'_db_host']));
$attr[] = array('label'=>'Database Name', 'cssClass'=>'mysql', 'type'=>'raw', 'value'=>CHtml::textField('dbName', $p[$type.'_db_name']));
$attr[] = array('label'=>'Database Username', 'cssClass'=>'mysql', 'type'=>'raw', 'value'=>CHtml::textField('dbUser', @$p['config'][$type.'_db_user']));
$attr[] = array('label'=>'Database Password', 'cssClass'=>'mysql', 'type'=>'raw', 'value'=>CHtml::textField('dbPass', @$p['config'][$type.'_db_pass']));
$attr[] = array('label'=>'', 'type'=>'raw', 'value'=>CHtml::submitButton('Save and Test'));

$this->widget('zii.widgets.CDetailView', array(
    'data'=>array(),
    'attributes'=>$attr,
)); 
echo CHtml::endForm();

echo CHtml::script('
    $("#driver").change(function() {
        sel = $(this).children("option:selected").val();
        $(".sqlite").toggle(sel == "sqlite");
        $(".mysql").toggle(sel == "mysql");
        $(".manual").toggle(sel == "manual");
    });
    $(function() {
        $(".manual > td, .sqlite > td").children(":text").width(450);
        $("#driver").change();
    });
');

?>
