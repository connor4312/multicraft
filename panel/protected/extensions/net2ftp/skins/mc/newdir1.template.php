<?php defined("NET2FTP") or die("Direct access to this location is not allowed."); ?>
<!-- Template /skins/mc/newdir1.template.php begin -->
<div class="form-group">
	<label class="col-md-3 control-label"><?php echo Yii::t('mc', "The new directories will be created in:"); ?></label>
	<div class="col-md-9"><input type="text" disabled class="form-control" value="<?php echo $net2ftp_globals["printdirectory"] ?>"></div>
</div>
<div class="form-group">
	<label class="col-md-3 control-label"><?php echo Yii::t('mc', "New directory name:"); ?></label>
	<div class="col-md-9"><input type="text" class="form-control" name="newNames[1]" /></div>
</div>
<!-- Template /skins/mc/newdir1.template.php end -->
