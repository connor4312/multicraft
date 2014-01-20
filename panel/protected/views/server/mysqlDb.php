<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle = Yii::app()->name . ' - '.Yii::t('mc', 'MySQL Database');

$this->breadcrumbs=array(
    Yii::t('mc', 'Servers')=>array('index'),
    $model->name=>array('view', 'id'=>$model->id),
    Yii::t('mc', 'MySQL Database'),
);

Yii::app()->getClientScript()->registerCoreScript('jquery');

if (!strlen(@$info[0]))
{
$this->menu=array(
    array(
        'label'=>Yii::t('mc', 'Create Database'),
        'url'=>'#',
        'icon'=>'mysql_new',
        'linkOptions'=>array(
            'submit'=>array('mysqlDb', 'cmd'=>'create', 'id'=>$model->id),
            'confirm'=>Yii::t('mc', 'This creates a new MySQL database for this server.'),
            'csrf'=>true,
        )
    ),
);
}
else
{
$this->menu=array(
    array(
        'label'=>Yii::t('mc', 'New Password'),
        'url'=>'#',
        'icon'=>'mysql_passwd',
        'linkOptions'=>array(
            'submit'=>array('mysqlDb', 'cmd'=>'passwd', 'id'=>$model->id),
            'confirm'=>Yii::t('mc', 'Generate a new password?'),
            'csrf'=>true,
        )
    ),
    array(
        'label'=>Yii::t('mc', 'Delete Database'),
        'url'=>'#',
        'icon'=>'mysql_del',
        'linkOptions'=>array(
            'submit'=>array('mysqlDb','cmd'=>'delete', 'id'=>$model->id),
            'confirm'=>Yii::t('mc', "Delete database?\n\nWARNING: Deletes all data and cannot be undone!"),
            'csrf'=>true,
        )
    ),
);
}
$this->menu[] = array(
    'label'=>Yii::t('mc', 'Back'),
    'url'=>array('server/view', 'id'=>$model->id),
    'icon'=>'back',
);
?>

<?php if(Yii::app()->user->hasFlash('server_error')): ?>
<div class="flash-error">
    <?php echo Yii::app()->user->getFlash('server_error'); ?>
</div>
<?php endif ?>

<?php
if (!strlen(@$info[0]))
{
    echo Yii::t('mc', 'There is currently no database associated with this server.');
    return;
}

$attribs = array();
$attribs[] = array('label'=>CHtml::label(Yii::t('mc', 'Host'), false), 'type'=>'raw',
    'value'=>CHtml::encode($model->mysqlHost));
$attribs[] = array('label'=>CHtml::label(Yii::t('mc', 'Name'), false), 'type'=>'raw',
    'value'=>CHtml::encode($info[0]));
$attribs[] = array('label'=>CHtml::label(Yii::t('mc', 'Username'), false), 'type'=>'raw',
    'value'=>CHtml::encode($info[0]));
$attribs[] = array('label'=>CHtml::label(Yii::t('mc', 'Password'), false), 'type'=>'raw',
    'value'=>CHtml::textField('pwd', $info[1], array('id'=>'pwdBox', 'readonly'=>'readonly')));
if (strlen($model->mysqlLink))
    $attribs[] = array('label'=>CHtml::label(Yii::t('mc', 'Administration Link'), false), 'type'=>'raw',
        'value'=>CHtml::link($model->mysqlLink, $model->mysqlLink, array('target'=>'_blank')));

?>

<?php
$this->widget('zii.widgets.CDetailView', array(
    'data'=>array(),
    'attributes'=>$attribs,
)); 

echo CHtml::script('$("#pwdBox").focus(function() { this.select(); });');            
?>

