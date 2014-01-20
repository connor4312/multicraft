<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle = Yii::app()->name.' - '.Yii::t('mc', 'Command List');

$this->breadcrumbs=array(
    Yii::t('mc', 'Servers')=>array('server/index'),
    Server::model()->findByPk($sv)->name=>array('server/view', 'id'=>$sv),
    Yii::t('mc', 'Commands'),
);

$this->menu=array(
    array(
        'label'=>Yii::t('mc', 'Create Command'),
        'url'=>array('create', 'sv'=>$sv),
        'icon'=>'command_new',
    ),
    array(
        'label'=>Yii::t('mc', 'Manage Commands'),
        'url'=>array('admin'),
        'visible'=>Yii::app()->user->isSuperuser(),
        'icon'=>'command',
    ),
    array(
        'label'=>Yii::t('mc', 'Back'),
        'url'=>array('server/view', 'id'=>$sv),
        'icon'=>'back',
    ),
);
?>

<?php

$columns = array(
    array('name'=>'name', 'type'=>'raw',
        'value'=>'CHtml::link(CHtml::encode($data->name), array("command/view", "id"=>$data->id))'),
    array('name'=>'level',
        'value'=>'User::getRoleLabel(User::getLevelRole($data->level))'),
    'chat',
);

if ($model->hasAttribute('hidden'))
{
    $columns[] = array('name'=>'hidden', 'value'=>'$data->hidden ? "'.Yii::t('mc', 'Yes').'" :
        "'.Yii::t('mc', 'No').'"', 'htmlOptions'=>array('style'=>'width: 40px'),
        'visible'=>Yii::app()->user->isSuperuser());
}

$this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'command-grid',
    'dataProvider'=>$model->search(),
    'filter'=>$model,
    'ajaxUpdate'=>false,
    'columns'=>$columns,
)); ?>
