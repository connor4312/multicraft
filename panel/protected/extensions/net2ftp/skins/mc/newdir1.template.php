<?php defined("NET2FTP") or die("Direct access to this location is not allowed."); ?>
<!-- Template /skins/mc/newdir1.template.php begin -->
<?php	echo Yii::t('mc', "The new directories will be created in <b>{dir}</b>.", array('{dir}'=>$net2ftp_globals["printdirectory"])); ?><br /><br />
<?php echo Yii::t('mc', "New directory name:"); ?> <input type="text" class="input" name="newNames[1]" /><br /><br />
<!-- Template /skins/mc/newdir1.template.php end -->
