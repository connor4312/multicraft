<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle = Yii::app()->name . ' - '.Yii::t('mc', 'Config Files');

$this->breadcrumbs=array(
    Yii::t('mc', 'Servers')=>array('index'),
    $model->name=>array('view', 'id'=>$model->id),
    Yii::t('mc', 'Config Files'),
);

$this->menu=array(
    array(
        'label'=>Yii::t('mc', 'Permissions Plugin'),
        'url'=>array('server/editPermissionsConfig', 'id'=>$model->id),
        'visible'=>$perm,
        'icon'=>'config',
    ),
    array(
        'label'=>Yii::t('mc', 'Back'),
        'url'=>array('server/view', 'id'=>$model->id),
        'icon'=>'back',
    ),
);
?>

<?php if(Yii::app()->user->hasFlash('server')): ?>
<div class="flash-success">
    <?php echo Yii::app()->user->getFlash('server'); ?>
</div>
<?php endif ?>

<?php if ($error): ?>
<div class="flash-error">
    <?php echo $error ?>
</div>
<?php endif ?>

<?php

$cols = array(
    array('name'=>'name', 'header'=>Yii::t('mc', 'Name'), 'type'=>'raw',
            'value'=>'CHtml::link(CHtml::encode($data["name"]), array("editConfig", "id"=>'.$model->id
            .', "config"=>$data["id"], "ro"=>$data["ro"], "file"=>$data["file"], "dir"=>$data["dir"]))'),
    array('name'=>'file', 'header'=>Yii::t('mc', 'File'), 'value'=>'$data["file"]'),
    array('name'=>'desc', 'header'=>Yii::t('mc', 'Description')),
);

$this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'configs-grid',
    'dataProvider'=>$dataProvider,
    'columns'=>$cols,
)); ?>



