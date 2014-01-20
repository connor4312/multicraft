<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle = Yii::app()->name . ' - '.Yii::t('mc', 'FTP File Access');

$this->breadcrumbs=array(
    Yii::t('mc', 'Servers')=>array('index'),
    $model->name=>array('view', 'id'=>$model->id),
    Yii::t('mc', 'FTP File Access'),
);

$this->menu=array(
    array(
        'label'=>Yii::t('mc', 'Back'),
        'url'=>array('server/view', 'id'=>$model->id),
        'icon'=>'back',
    ),
);

if (isset($dmnInfo['error']))
{
    echo CHtml::encode($dmnInfo['error']);
    return;
}
?>

<?php echo Yii::t('mc', 'You can use the following information to access your files with any FTP client.') ?><br/>
<br/>

<?php

$attribs = array();
$attribs[] = array('label'=>CHtml::label(Yii::t('mc', 'Host'), false), 'type'=>'raw',
    'value'=>'<div id="ftp_ip">'.CHtml::encode($dmnInfo['ip']).'</div>');
$attribs[] = array('label'=>CHtml::label(Yii::t('mc', 'Port'), false), 'type'=>'raw',
    'value'=>'<div id="ftp_port">'.CHtml::encode($dmnInfo['port']).'</div>');
$attribs[] = array('label'=>CHtml::label(Yii::t('mc', 'FTP Username'), false), 'type'=>'raw',
    'value'=>'<div id="username">'.CHtml::encode(Yii::app()->user->name).'.'.$model->id.'</div>');
$attribs[] = array('label'=>CHtml::label(Yii::t('mc', 'FTP Password'), 'password'), 'type'=>'raw',
    'value'=>CHtml::label(Yii::t('mc', 'Your Multicraft Password'), ''));
?>

<?php
$this->widget('zii.widgets.CDetailView', array(
    'data'=>array(),
    'attributes'=>$attribs,
)); 
?>

