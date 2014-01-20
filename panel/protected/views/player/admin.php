<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle = Yii::app()->name . ' - '.Yii::t('mc', 'Manage Players');

$this->breadcrumbs=array(
    Yii::t('mc', 'Servers')=>array('server/index'),
    Yii::t('mc', 'Players'),
);

$this->menu=array(
    array(
        'label'=>Yii::t('mc', 'Create Player'),
        'url'=>array('create'),
        'icon'=>'player_new',
    ),
    array(
        'label'=>Yii::t('mc', 'Back'),
        'url'=>array('server/index'),
        'icon'=>'back',
    ),
);

$this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'player-grid',
    'dataProvider'=>$model->search(),
    'filter'=>$model,
    'ajaxUpdate'=>false,
    'columns'=>array(
        array('name'=>'server_id', 'type'=>'raw',
            'value'=>'($s = Server::model()->findByPk($data->server_id)) ? CHtml::link(CHtml::encode($s->name), array("server/view", "id"=>$s->id)) : ""'),
        array('name'=>'name', 'type'=>'raw',
            'value'=>'CHtml::link(CHtml::encode($data->name), array("player/view", "id"=>$data->id))'),
        array('name'=>'level','headerHtmlOptions'=>array('width'=>'90'),
            'value'=>'User::getRoleLabel(User::getLevelRole($data->level))'),
        array('name'=>'lastseen', 'value'=>'$data->lastseen ? @date("'.Yii::t('mc', 'd. M Y, H:i').'", (int)$data->lastseen) : "'.Yii::t('mc', 'Never').'"'),
        array('name'=>'banned','headerHtmlOptions'=>array('width'=>'30'),),
        'ip',
    ),
)); ?>
