<?php $mail->setSubject(Yii::t('mc', 'Welcome to Multicraft!')) ?>
<?php echo Yii::t('mc', 'Hello {name},', array('{name}'=>$name)) ?>


<?php echo Yii::t('mc', 'Welcome to Multicraft! The login information for your new Multicraft account is:') ?>


<?php echo Yii::t('mc', 'Username:') ?> <?php echo $name ?>

<?php echo Yii::t('mc', 'Password:') ?> <?php echo $password ?>


<?php echo Yii::t('mc', 'You can login to Multicraft here:') ?>

<?php echo $panel ?>


<?php echo Yii::t('mc', 'To edit your profile visit:') ?>

<?php echo $host ?><?php echo str_replace('api.php', 'index.php', CHtml::normalizeUrl(array('user/view', 'id'=>$id))) ?>


