<?php $mail->setSubject(Yii::t('mc', 'Multicraft - New Server Assigned')) ?>
<?php echo Yii::t('mc', 'Hello {name},', array('{name}'=>$user_name)) ?>


<?php echo Yii::t('mc', 'A new server has been assigned to you! You can control and edit it here:') ?>

<?php echo $host.str_replace('api.php', 'index.php', CHtml::normalizeUrl(array('server/view', 'id'=>$server_id))) ?>


<?php echo Yii::t('mc', 'To see a list of all of your servers visit:') ?>

<?php echo $host.str_replace('api.php', 'index.php', CHtml::normalizeUrl(array('server/index', 'my'=>1))) ?>

