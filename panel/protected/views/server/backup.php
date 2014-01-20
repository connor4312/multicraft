<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle = Yii::app()->name . ' - '.Yii::t('mc', 'Backup World');

$this->breadcrumbs=array(
    Yii::t('mc', 'Servers')=>array('index'),
    $model->name=>array('view', 'id'=>$model->id),
    Yii::t('mc', 'Backup'),
);

$this->menu=array(
    array(
        'label'=>Yii::t('mc', 'Restore'),
        'url'=>array('server/restore', 'id'=>$model->id),
        'icon'=>'restore',
        'visible'=>Yii::app()->user->can($model->id, 'start backup'),
    ),
    array(
        'label'=>Yii::t('mc', 'Back'),
        'url'=>array('server/view', 'id'=>$model->id),
        'icon'=>'back',
    ),
);
?>

<?php echo CHtml::beginForm('', '', array('id'=>'download_form')) ?>
    <input type="hidden" id="ajax" name="ajax" value="download"/>
<?php echo CHtml::endForm() ?>

<div id="backup-ajax"><?php echo $data['backup'] ?></div>

<?php $this->printRefreshScript(); ?>
<?php echo CHtml::script('
    function backup_response(data)
    {
        if (data)
            alert(data);
        setTimeout(function() { refresh("backup"); }, 500);
    }

    function backup_download()
    {
        $("#download_form").submit();
        return false;
    }
    '); ?>
