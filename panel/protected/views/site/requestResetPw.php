<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle=Yii::app()->name . ' - '.Yii::t('mc', 'Reset Password');
$this->breadcrumbs=array(
    Yii::t('mc', 'Reset Password'),
);

$this->menu=array(
    array(
        'label'=>Yii::t('mc', 'Back'),
        'url'=>array('site/login'),
        'icon'=>'back',
    ),
);
?>

<?php if (Yii::app()->user->hasFlash('reset-success')): ?>
<div class="flash-success">
    <?php echo Yii::app()->user->getFlash('reset-success'); ?>
</div>
<?php elseif (Yii::app()->user->hasFlash('reset-error')): ?>
<div class="flash-error">
    <?php echo Yii::app()->user->getFlash('reset-error'); ?>
</div>
<?php elseif ($state =='info'): ?>
<div class="flash-error">
    <?php echo Yii::t('mc', 'Invalid request') ?>
</div>
<?php endif ?>

<?php if ($state =='info') return; ?>

<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
    'id'=>'reset-form',
    'enableAjaxValidation'=>false,
)); ?>

    <div class="row">
        <?php echo $form->labelEx($model,'email'); ?>
        <?php echo $form->textField($model,'email'); ?>
        <?php echo $form->error($model,'email'); ?>
    </div>

    <div class="row buttons">
        <?php echo CHtml::submitButton(Yii::t('mc', 'Send Reset Link')); ?>
    </div>

<?php $this->endWidget(); ?>
</div>

