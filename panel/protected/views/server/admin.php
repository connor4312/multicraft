<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle = Yii::app()->name . ' - '.Yii::t('mc', 'Manage Servers');

$this->breadcrumbs=array(
    Yii::t('mc', 'Servers')=>array('index'),
    Yii::t('mc', 'Manage'),
);

$this->menu=array(
    array(
        'label'=>Yii::t('mc', 'Create Server'),
        'url'=>array('create'),
        'icon'=>'create',
    ),
    array(
        'label'=>Yii::t('mc', 'Back'),
        'url'=>array('server/index'),
        'icon'=>'back',
    ),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
    $('.search-form').toggle();
    return false;
});
$('.search-form form').submit(function(){
    $.fn.yiiGridView.update('server-grid', {
        data: $(this).serialize()
    });
    return false;
});
");
?>

<p>
<?php echo Yii::t('mc', 'You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b> or <b>=</b>) at the beginning of each of your search values to specify how the comparison should be done.') ?>
</p>

<?php echo CHtml::link(Yii::t('mc', 'Advanced Search'),'#',array('class'=>'search-button')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
    'model'=>$model,
)); ?>
</div><!-- search-form -->

<?php $this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'server-grid',
    'dataProvider'=>$model->search(),
    'filter'=>$model,
    'columns'=>array(
        array('name'=>'id', 'headerHtmlOptions'=>array('width'=>'30')),
        array('name'=>'name', 'type'=>'raw',
            'value'=>'CHtml::link(CHtml::encode($data->name), array("server/view", "id"=>$data->id))'),
        'ip',
        array('name'=>'port','headerHtmlOptions'=>array('width'=>'30'),),
        array('name'=>'searchOwner', 'header'=>Yii::t('mc', 'Owner'), 'type'=>'raw', 'value'=>'($u = User::model()->findByPk($data->owner)) ? CHtml::link(CHtml::encode(substr($u->name, 0, 20)), array("user/view", "id"=>$u->id)) : ""', 'headerHtmlOptions'=>array('width'=>'80')),
        array('name'=>'players','headerHtmlOptions'=>array('width'=>'30'),),
        array('name'=>'memory', 'headerHtmlOptions'=>array('width'=>'30')),
        array('name'=>'daemon_id','headerHtmlOptions'=>array('width'=>'10'), 'type'=>'raw',
            'value'=>'($d = $data->daemon_id) ? CHtml::link(CHtml::encode($d), array("daemon/status", "id"=>$d)) : ""'),
        array('name'=>'suspended','headerHtmlOptions'=>array('width'=>'10'),),
    ),
)); ?>
