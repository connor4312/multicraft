<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle = Yii::app()->name.' - '.Yii::t('mc', 'Manage Commands');

$this->breadcrumbs=array(
    Yii::t('mc', 'Servers')=>array('server/index'),
    Yii::t('mc', 'Commands'),
);

$this->menu=array(
    array(
        'label'=>Yii::t('mc', 'Create Command'),
        'url'=>array('create'),
        'icon'=>'command_new',
    ),
    array(
        'label'=>Yii::t('mc', 'Back'),
        'url'=>array('server/index'),
        'icon'=>'back',
    ),
);


$this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'command-grid',
    'dataProvider'=>$model->search(),
    'filter'=>$model,
    'ajaxUpdate'=>false,
    'columns'=>array(
        array('name'=>'server_id', 'type'=>'raw',
            'value'=>'($s = Server::model()->findByPk($data->server_id)) ? CHtml::link(CHtml::encode($s->id." (".$s->name.")"), array("server/view", "id"=>$s->id)) : "0 (".Yii::t("mc", "Global").")"'),
        array('name'=>'name', 'type'=>'raw',
            'value'=>'CHtml::link(CHtml::encode($data->name), array("command/view", "id"=>$data->id))'),
        array('name'=>'level','headerHtmlOptions'=>array('width'=>'90'),
            'value'=>'User::getRoleLabel(User::getLevelRole(@$data->level))'),
        'chat',
        'run',
    ),
)); ?>
