<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle=Yii::app()->name . ' - '.Yii::t('mc', 'Login');
$this->breadcrumbs=array(
    Yii::t('mc', 'Login'),
);
?>

<?php if (!Yii::app()->params['register_disabled']): ?>
<p><?php echo CHtml::link(Yii::t('mc', 'Register here'), array('site/register')) ?> <?php echo Yii::t('mc', 'if you don\'t have an account yet.') ?></p>
<?php endif ?>

<?php if (Yii::app()->user->hasFlash('login')): ?>
<div class="flash-success">
    <?php echo Yii::app()->user->getFlash('login'); ?>
</div>
<?php endif ?>

<?php if (Yii::app()->params['demo_mode'] != 'enabled'): ?>

<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
    'id'=>'login-form',
    'enableAjaxValidation'=>false,
)); ?>

    <div class="row">
        <?php echo $form->labelEx($model,'name'); ?>
        <?php echo $form->textField($model,'name'); ?>
        <?php echo $form->error($model,'name'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'password'); ?>
        <?php echo $form->passwordField($model,'password'); ?>
        <?php echo $form->error($model,'password'); ?>
        <?php if (Yii::app()->params['reset_token_hours'] > 0): ?>
        <br/>
        <?php echo CHtml::link(Yii::t('mc', 'Forgot password?'), array('site/requestResetPw'), array('style'=>'font-size: 11px')); ?>
        <?php endif ?>
    </div>

    <div class="row rememberMe">
        <?php echo $form->checkBox($model,'rememberMe'); ?>
        <?php echo $form->label($model,'rememberMe'); ?>
        <?php echo $form->error($model,'rememberMe'); ?>
    </div>

    <div class="row rememberMe">
        <?php echo $form->checkBox($model,'ignoreIp'); ?>
        <?php echo $form->label($model,'ignoreIp'); ?>
        <?php echo $form->error($model,'ignoreIp'); ?>
    </div>

    <div class="row buttons">
        <?php echo CHtml::submitButton(Yii::t('mc', 'Login')); ?>
    </div>

<?php $this->endWidget(); ?>
</div><!-- form -->

<?php else: ?>
<h1>Demo mode</h1>
<table>
<tr>
<td>
<?php echo CHtml::beginForm() ?>
<?php echo CHtml::hiddenField('LoginForm[name]', 'admin') ?>
<?php echo CHtml::hiddenField('LoginForm[password]', 'admin') ?>
<?php echo CHtml::submitButton('Log me in as Administrator', array('style'=>'width: 180px')); ?>
<?php echo CHtml::endForm() ?>
</td>
<td>
Create servers &amp; users
</td>
</tr>
<tr>
<td>
<?php echo CHtml::beginForm() ?>
<?php echo CHtml::hiddenField('LoginForm[name]', 'owner') ?>
<?php echo CHtml::hiddenField('LoginForm[password]', 'owner') ?>
<?php echo CHtml::submitButton('Log me in as Server Owner', array('style'=>'width: 180px')); ?>
<?php echo CHtml::endForm() ?>
</td>
<td>
Edit server settings, assign permissions to users/players, define custom commands
</td>
</tr>
<tr>
<td>
<?php echo CHtml::beginForm() ?>
<?php echo CHtml::hiddenField('LoginForm[name]', 'user') ?>
<?php echo CHtml::hiddenField('LoginForm[password]', 'user') ?>
<?php echo CHtml::submitButton('Log me in as normal User', array('style'=>'width: 180px')); ?>
<?php echo CHtml::endForm() ?>
</td>
<td>
Edit assigned players, use functions for assigned player
</td>
</tr>
</table>
<br/>
<br/>
<div class="infoBox">
<b>Note</b><br/>
Servers are not running and can't be stopped/restarted.<br/>
</div>

<?php endif ?>
