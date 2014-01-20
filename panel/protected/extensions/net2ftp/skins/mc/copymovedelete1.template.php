<?php defined("NET2FTP") or die("Direct access to this location is not allowed."); ?>
<!-- Template /skins/mc/copymovedelete1.template.php begin -->

<?php /* ----- Copy or Move: print header table ----- */ ?>
<?php	if ($net2ftp_globals["state2"] == "copy" || $net2ftp_globals["state2"] == "move") { ?>
		<table style="border-color: #000000; border-style: solid; border-width: 1px; padding: 10px;">
			<tr><td>
				<div style="font-size: 80%; margin-bottom: 10px"><?php echo Yii::t('mc', "To set the target for all items use this field and click on \"Move all to\"."); ?></div>
				<input type="button" class="extralongbutton" value="<?php echo Yii::t('mc', "Move all to"); ?>" onclick="CopyValueToAll(document.forms['CopyMoveDeleteForm'], document.forms['CopyMoveDeleteForm'].headerDirectory, 'targetdirectory');" /> &nbsp; 
				<input type="text" style="width: 300px;" name="headerDirectory" value="<?php echo $net2ftp_globals["directory_html"]; ?>" />
				<?php printActionIcon("listdirectories", "createDirectoryTreeWindow('" . $net2ftp_globals["directory_js"] . "','CopyMoveDeleteForm.headerDirectory');"); ?>
			</td></tr>
		</table>
		<br />
<?php	} // end if
	/* ----- Delete: print warning message ----- */
	elseif ($net2ftp_globals["state2"] == "delete") { ?>
		<?php echo Yii::t('mc', "Are you sure you want to delete these items?"); ?><br />
		<?php echo Yii::t('mc', "This will delete the contents of the selected directories!"); ?><br /><br />
<?php	} // end elseif ?>

<?php /* ----- List of selected entries ----- */ ?>
<?php	for ($i=1; $i<=sizeof($list["all"]); $i++) { ?>
<?php		printDirFileProperties($i, $list["all"][$i], "hidden", ""); ?>
		<input type="hidden" name="list[<?php echo $i; ?>][sourcedirectory]" value="<?php echo $net2ftp_globals["directory_html"]; ?>" />
<?php		if     ($net2ftp_globals["state2"] == "copy") {
			if     ($list["all"][$i]["dirorfile"] == "d") { echo Yii::t('mc', "Copy directory <b>{dir}</b> to:", array('{dir}'=>$list["all"][$i]["dirfilename"])); }
			elseif ($list["all"][$i]["dirorfile"] == "-") { echo Yii::t('mc', "Copy file <b>{file}</b> to:", array('{file}'=>$list["all"][$i]["dirfilename"])); }
			elseif ($list["all"][$i]["dirorfile"] == "l") { echo Yii::t('mc', "Copy symlink <b>{lnk}</b> to:", array('{lnk}'=>$list["all"][$i]["dirfilename"])); }
		}
		elseif ($net2ftp_globals["state2"] == "move") {
			if     ($list["all"][$i]["dirorfile"] == "d") { echo Yii::t('mc', "Move directory <b>{dir}</b> to:", array('{dir}'=>$list["all"][$i]["dirfilename"])); }
			elseif ($list["all"][$i]["dirorfile"] == "-") { echo Yii::t('mc', "Move file <b>{file}</b> to:", array('{file}'=>$list["all"][$i]["dirfilename"])); }
			elseif ($list["all"][$i]["dirorfile"] == "l") { echo Yii::t('mc', "Move symlink <b>{lnk}</b> to:", array('{lnk}'=>$list["all"][$i]["dirfilename"])); }
		}
		elseif ($net2ftp_globals["state2"] == "delete") {
			if     ($list["all"][$i]["dirorfile"] == "d") { echo Yii::t('mc', "Directory <b>{dir}</b>", array('{dir}'=>$list["all"][$i]["dirfilename"])); }
			elseif ($list["all"][$i]["dirorfile"] == "-") { echo Yii::t('mc', "File <b>{file}</b>", array('{file}'=>$list["all"][$i]["dirfilename"])); }
			elseif ($list["all"][$i]["dirorfile"] == "l") { echo Yii::t('mc', "Symlink <b>{lnk}</b>", array('{lnk}'=>$list["all"][$i]["dirfilename"])); }
		} 
?>
		<br />
<?php /* ----- Copy or move: ask for options ----- */ ?>
<?php		if ($net2ftp_globals["state2"] == "copy" || $net2ftp_globals["state2"] == "move") { ?>
			<table>
				<tr>
					<td><?php echo Yii::t('mc', "Target directory:"); ?></td>
					<td>
						<input type="text" style="width: 300px;" name="list[<?php echo $i; ?>][targetdirectory]" value="<?php echo $net2ftp_globals["directory_html"]; ?>" />
					</td>
				</tr>
				<tr><td>
					<?php echo Yii::t('mc', "Target name:"); ?>      </td><td><input type="text" class="input" name="list[<?php echo $i; ?>][newname]" value="<?php echo $list["all"][$i]["dirfilename_html"]; ?>" />
				</td></tr>
			</table><br />
<?php		} // end if ?>
<?php	} // end for ?>
<br/>
<!-- Template /skins/mc/copymovedelete1.template.php end -->
