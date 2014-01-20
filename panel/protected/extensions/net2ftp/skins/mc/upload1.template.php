<?php defined("NET2FTP") or die("Direct access to this location is not allowed."); ?>
<!-- Template /skins/mc/upload1.template.php begin -->
<?php echo Yii::t('mc', 'Upload to directory'); ?> <input type="text" style="width: 300px;" name="directory" value="<?php echo $net2ftp_globals["directory_html"]; ?>" />
<?php
function format_bytes($size) {
    $units = array(' B', ' KB', ' MB', ' GB', ' TB');
    for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
    return round($size, 2).$units[$i];
}
?>
<?php printActionIcon("listdirectories", "createDirectoryTreeWindow('" . $net2ftp_globals["directory_js"] . "','" . $formname . ".directory');"); ?>
<br/>
<br/>
<br/>
<table border="1" cellspacing="0" cellpadding="0">
<tr>
    <td colspan="2" style="vertical-align: top;vertical-align: top;  width: 50%">
        <b><?php echo Yii::t('mc', 'Restrictions'); ?></b>
        <div style="font-size: 90%">
        <ul>
            <li> <?php echo Yii::t('mc', 'The maximum size of one file is <b>{size}</b> (total {total})', array('{size}'=>format_bytes($net2ftp_settings["max_filesize"]), '{total}'=>format_bytes($net2ftp_settings["max_post_size"]))); ?></li>
            <li> <?php echo Yii::t('mc', 'The maximum upload time is <b>{time} seconds</b>', array('{time}'=>$max_execution_time)) ?></li>
            <li> <?php echo Yii::t('mc', 'If the destination file already exists, it will be overwritten') ?></li>
        </ul>
    </td>
</tr>
<tr>
    <td style="vertical-align: top; padding-left: 0">
        <div class="header31"><b><?php echo $t = Yii::t('mc', 'Files to upload'); ?></b></div>
        <a href="#" class="addf" onclick="javascript:add_file2('file'); $('.addf').html($('.addf').html()); return false"><?php echo Yii::t('mc', 'Add Field') ?></a>
    </td>
    <td style="vertical-align: top; padding-left: 0">
        <div class="header31"><b><?php echo Yii::t('mc', 'Archives to upload and unpack'); ?></b></div>
        <a href="#" class="addf" onclick="javascript:add_file2('archive'); $('.addf').html($('.addf').html()); return false"><?php echo Yii::t('mc', 'Add Field') ?></a>
    </td>
</tr>
<tr>
    <td style="vertical-align: top; padding-left: 0" id="files">
        <input type="file" class="uploadinputbutton" maxsize="<?php echo $net2ftp_settings["max_filesize"]; ?>" name="file[]" onchange="add_file2('file');" /><br/>
    </td>
    <td style="vertical-align: top; padding-left: 0" id="archives">
        <input type="file" class="uploadinputbutton" maxsize="<?php echo $net2ftp_settings["max_filesize"]; ?>" name="archive[]" onchange="add_file2('archive');" /><br/>
    </td>
</tr>
</table>
</div><br />
<!-- Template /skins/mc/upload1.template.php end -->
