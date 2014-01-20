<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle = Yii::app()->name . ' - '.Yii::t('mc', 'Plugins');

$this->breadcrumbs=array(
    Yii::t('mc', 'Servers')=>array('index'),
    $model->name=>array('view', 'id'=>$model->id),
    Yii::t('mc', 'Plugins'),
);

$this->menu=array(
    array(
        'label'=>Yii::t('mc', 'Back'),
        'url'=>array('server/view', 'id'=>$model->id),
        'icon'=>'back',
    ),
);
?>

<?php if(Yii::app()->user->hasFlash('server')): ?>
<div class="flash-error">
    <?php echo Yii::app()->user->getFlash('server'); ?>
</div>
<?php endif ?>
<?php if(Yii::app()->user->hasFlash('plugin_unpack')): ?>
<div class="flash-success">
    <?php echo Yii::app()->user->getFlash('plugin_unpack'); ?>
</div>
<?php endif ?>
<?php if (!$haveItems): ?>
    <?php echo Yii::t('mc', 'There are no plugins available for the "JAR File" currently in use by the server.') ?>
<?php else: ?>

<?php

$cols = array(
    array('name'=>'file', 'header'=>Yii::t('mc', 'File'), 'value'=>'$data["displayFile"]'),
    array('name'=>'desc', 'header'=>Yii::t('mc', 'Description'), 'type'=>'html'),
    array('name'=>'status', 'header'=>Yii::t('mc', 'Status'), 'headerHtmlOptions'=>array('width'=>'30'),
        'htmlOptions'=>array('style'=>'text-align: center'), 'type'=>'raw',
        'value'=>'Theme::img("icons/plugin".$data["status"].".png", $data["status_alt"])'),
    array('header'=>'', 'headerHtmlOptions'=>array('width'=>'120'),
        'htmlOptions'=>array('style'=>'text-align: center'), 'type'=>'raw', 'value'=>'$data["action"]'),
);

echo CHtml::css('.topalign td { vertical-align: top }' );
$this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'configs-grid',
    'filter'=>$filter,
    'ajaxUpdate'=>false,
    'rowCssClass'=>array('even topalign', 'odd topalign'),
    'dataProvider'=>$dataProvider,
    'columns'=>$cols,
)); ?>

<?php endif ?>

