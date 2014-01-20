<?php $mail->setSubject(Yii::t('mc', 'Multicraft - Password Reset')) ?>
<?php echo Yii::t('mc', 'Hello {name},', array('{name}'=>$name)) ?>


<?php echo Yii::t('mc', 'A password reset has been requested for your Multicraft account at {url}.', array('{url}'=>$panel)) ?>

<?php echo Yii::t('mc', 'To proceed with the password reset, please use the following link within the next {nr} hours:', array('{nr}'=>Yii::app()->params['reset_token_hours'])) ?>


<?php echo $host ?><?php echo CHtml::normalizeUrl(array('site/resetPw', 'l'=>$l)) ?>


<?php echo Yii::t('mc', 'In case the above link doesn\'t work you can enter the following token manually:') ?>

<?php echo Yii::t('mc', 'Token') ?>: <?php echo $l ?>

<?php echo $host ?><?php echo CHtml::normalizeUrl(array('site/resetPw')) ?>


<?php echo Yii::t('mc', 'If you did not request a password reset you can either just let the reset token expire or deactivate it using the following link:') ?>

<?php echo $host ?><?php echo CHtml::normalizeUrl(array('site/resetPw', 'deactivate'=>'true', 'l'=>$l)) ?>
