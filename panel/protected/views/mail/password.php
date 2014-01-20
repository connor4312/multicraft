<?php $mail->setSubject(Yii::t('mc', 'Multicraft - Password Changed')) ?>
<?php echo Yii::t('mc', 'Hello {name},', array('{name}'=>$name)) ?>


<?php echo Yii::t('mc', 'Your Multicraft password has been changed. The new login information for your Multicraft account is:') ?>


<?php echo Yii::t('mc', 'Username:') ?> <?php echo $name ?>

<?php echo Yii::t('mc', 'Password:') ?> <?php echo $password ?>


<?php echo Yii::t('mc', 'You can login to Multicraft here:') ?>

<?php echo $panel ?>


<?php echo Yii::t('mc', 'To edit your profile visit:') ?>

<?php echo $host ?><?php echo CHtml::normalizeUrl(array('user/view', 'id'=>$id)) ?>


