<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle=Yii::app()->name . ' - '.Yii::t('mc', 'Support');
$this->breadcrumbs=array(
    Yii::t('mc', 'Support'),
);
?>

<?php if(Yii::app()->user->hasFlash('report')): ?>

<div class="flash-success">
    <?php echo Yii::app()->user->getFlash('report'); ?>
</div>

<?php else: ?>

<p>
<?php echo Yii::t('mc', 'If you have questions regarding our server management software or if you find a bug please contact us using this form or by sending an email to {email}.', array('{email}'=>CHtml::link(Yii::app()->params['admin_email'], 'mailto:'.Yii::app()->params['admin_email']))) ?><br/>
<?php echo Yii::t('mc', 'In case this is a bug report please include all the error messages you encountered and a short description of how to reproduce the bug.') ?>
</p>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm'); ?>

    <p class="note"><?php echo Yii::t('mc', 'Fields with <span class="required">*</span> are required.') ?></p>

    <?php echo $form->errorSummary($model); ?>

    <div class="row">
        <?php echo $form->labelEx($model,'name'); ?>
        <?php echo $form->textField($model,'name'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'email'); ?>
        <?php echo $form->textField($model,'email'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'report'); ?>
        <?php echo $form->textArea($model,'report',array('rows'=>9, 'cols'=>70)); ?>
    </div>

    <?php if(CCaptcha::checkRequirements()): ?>
    <div class="row">
        <?php echo $form->labelEx($model,'verifyCode'); ?>
        <div>
        <?php $this->widget('CCaptcha'); ?><br/>
        <?php echo $form->textField($model,'verifyCode'); ?>
        </div>
        <div class="hint"><?php echo Yii::t('mc', 'Please enter the letters as they are shown in the image above.') ?>
        <br/><?php echo Yii::t('mc', 'Letters are not case-sensitive.') ?></div>
    </div>
    <?php endif; ?>

    <div class="row buttons">
        <?php echo CHtml::submitButton(Yii::t('mc', 'Submit')); ?>
    </div>

<?php $this->endWidget(); ?>

</div><!-- form -->

<?php endif; ?>
