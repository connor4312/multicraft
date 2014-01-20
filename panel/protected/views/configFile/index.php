<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle = Yii::app()->name . ' - '.Yii::t('mc', 'Config File Settings');

$this->breadcrumbs=array(
    Yii::t('mc', 'Settings')=>array('daemon/index'),
    Yii::t('mc', 'Config File Settings')=>array('index'),
    Yii::t('mc', 'Manage'),
);

$this->menu=array(
    array(
        'label'=>Yii::t('mc', 'New Config File Setting'),
        'url'=>array('create'),
        'icon'=>'config'
    ),
    array(
        'label'=>Yii::t('mc', 'Back'),
        'url'=>array('daemon/index'),
        'icon'=>'back'
    ),
);

?>

<?php $this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'config-file-grid',
    'dataProvider'=>$model->search(),
    'filter'=>$model,
    'columns'=>array(
        array('name'=>'name', 'type'=>'raw',
            'value'=>'CHtml::link(CHtml::encode($data->name), array("configFile/view", "id"=>$data->id))'),
        'description',
        'file',
    ),
)); ?>
