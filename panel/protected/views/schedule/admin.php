<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle = Yii::app()->name . ' - '.Yii::t('mc', 'Manage Tasks');

$this->breadcrumbs=array(
    Yii::t('mc', 'Servers')=>array('server/index'),
    Yii::t('mc', 'Scheduled Tasks'),
);

$this->menu=array(
    array(
        'label'=>Yii::t('mc', 'New Task'),
        'url'=>array('create'),
        'icon'=>'schedule_new',
    ),
    array(
        'label'=>Yii::t('mc', 'Back'),
        'url'=>array('server/index'),
        'icon'=>'back',
    ),
);
?>

<?php $this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'schedule-grid',
    'dataProvider'=>$model->search(),
    'filter'=>$model,
    'ajaxUpdate'=>false,
    'columns'=>array(
        array('name'=>'server_id', 'type'=>'raw',
            'value'=>'($s = Server::model()->findByPk($data->server_id)) ? CHtml::link(CHtml::encode($s->id." (".$s->name.")"), array("server/view", "id"=>$s->id)) : "0 (".Yii::t("mc", "Global").")"'),
        array('name'=>'name', 'type'=>'raw',
            'value'=>'CHtml::link(CHtml::encode($data->name), array("schedule/view", "id"=>$data->id))'),
        array('name'=>'scheduled_ts', 'value'=>'$data->scheduled_ts ? @date("'.Yii::t('mc', 'd. M Y, H:i').'", (int)$data->scheduled_ts) : "'.Yii::t('mc', 'Not Scheduled').'"'),
        array('name'=>'interval', 'value'=>'$data->intervalString'),
        array('name'=>'status', 'value'=>'@Schedule::getStatusValues($data->status)'),
        array('name'=>'last_run_ts', 'value'=>'$data->last_run_ts ? @date("'.Yii::t('mc', 'd. M Y, H:i').'", (int)$data->last_run_ts) : "'.Yii::t('mc', 'Never').'"'),
    ),
)); ?>
