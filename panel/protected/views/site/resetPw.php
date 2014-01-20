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

?>

<?php if (!strlen($l)): ?>

<div class="form">
<?php echo CHtml::beginForm() ?>

    <div class="row">
        <?php echo CHtml::label(Yii::t('mc', 'Token'), 'l') ?>
        <?php echo CHtml::textField('l', $l) ?>
    </div>

    <div class="row buttons">
        <?php echo CHtml::submitButton(Yii::t('mc', 'Continue')); ?>
    </div>

<?php echo CHtml::endForm() ?>
</div>

<?php else: ?>

<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
    'id'=>'reset-form',
    'enableAjaxValidation'=>false,
)); ?>

    <div class="row">
        <?php echo $form->labelEx($model,'password'); ?>
        <?php echo $form->passwordField($model,'password'); ?>
        <?php echo $form->error($model,'password'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'confirmPassword'); ?>
        <?php echo $form->passwordField($model,'confirmPassword'); ?>
        <?php echo $form->error($model,'confirmPassword'); ?>
    </div>

    <div class="row buttons">
        <?php echo CHtml::hiddenField('l', $l) ?>
        <?php echo CHtml::submitButton(Yii::t('mc', 'Reset Password')); ?>
    </div>

<?php $this->endWidget(); ?>
</div>

<?php endif ?>
