<?php defined("NET2FTP") or die("Direct access to this location is not allowed."); ?>
<!-- Template /skins/mc/rename1.template.php begin -->
<?php for ($i=1; $i<=sizeof($list["all"]); $i++) { ?>
<?php		printDirFileProperties($i, $list["all"][$i], "hidden", ""); ?>
<div class="form-group">
	<label class="col-md-3 control-label"><?php echo Yii::t('mc', "Old name: "); ?></label>
	<div class="col-md-9"><input type="text" disabled class="form-control" value="<?php echo $list["all"][$i]["dirfilename"]; ?>"></div>
</div>
<div class="form-group">
	<label class="col-md-3 control-label"><?php echo Yii::t('mc', "New name: "); ?></label>
	<div class="col-md-9"><input type="text" class="form-control" name="newNames[<?php echo $i; ?>]" value="<?php echo $list["all"][$i]["dirfilename_html"]; ?>" /></div>
</div>
<?php } // end for ?>
<!-- Template /skins/mc/rename1.template.php end -->
