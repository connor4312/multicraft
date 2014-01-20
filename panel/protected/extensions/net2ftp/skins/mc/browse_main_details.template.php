<?php defined("NET2FTP") or die("Direct access to this location is not allowed."); ?>
<?php

global $controller;
$controller->menu = array(
    array('label'=>Yii::t('mc', 'Create Directory'), 'url'=>"javascript:submitBrowseForm('$directory_js', '', 'newdir', '');"),
    array('label'=>Yii::t('mc', 'Create File'), 'url'=>"javascript:submitBrowseForm('$directory_js', '', 'edit', 'newfile');"),
    array('label'=>Yii::t('mc', 'Upload'), 'url'=>"javascript:submitBrowseForm('$directory_js', '', 'upload', '');"),
    array('label'=>Yii::t('mc', 'Back'), 'url'=>array('ftpClient/login', 'id'=>@$_GET['id']), 'icon'=>'back'),
);

function format_bytes($size) {
    $units = array(' B', ' KB', ' MB', ' GB', ' TB');
    for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
    return round($size, 2).$units[$i];
}
?>
<!-- Template /skins/mc/browse_main_details.template.php begin -->

<script type="text/javascript"><!--\n"; 
function setColor_js(i, checkbox_hidden) {
    // i contains the row number
    // checkbox_hidden determines if the row has a checkbox, or hidden properties

// Set the colors for the rows
    if (i%2 == 1) { bgcolor_true = '#ABABAB'; fontcolor_true = '#000000'; bgcolor_false = '#f4f4f4'; fontcolor_false = '#000000'; }
    else          { bgcolor_true = '#ABABAB'; fontcolor_true = '#000000'; bgcolor_false = '#f4f4f4'; fontcolor_false = '#000000'; }

// Checkbox ==> set the colors depending on the checkbox status
// Hidden ==> set the colors as for an unchecked checkbox
    row_id = 'row' + i;
    checkbox_id = 'list_' + i + '_dirfilename';
    if (document.getElementById) {
        if (checkbox_hidden == 'checkbox' && document.getElementById(checkbox_id).checked == true) { 
            document.getElementById(row_id).style.background = bgcolor_true;  document.getElementById(row_id).style.color = fontcolor_true; 
        } else { 
            document.getElementById(row_id).style.background = bgcolor_false; document.getElementById(row_id).style.color = fontcolor_false; 
        }
    }
    else if (document.all) {
        if (checkbox_hidden == 'checkbox' && document.all[checkbox_id].checked == true) { 
            document.all[row_id].style.background = bgcolor_true;  document.all[row_id].style.color = fontcolor_true; 
        } else { 
            document.all[row_id].style.background = bgcolor_false; document.all[row_id].style.color = fontcolor_false; 
        }
    }
}
//--></script>

    <div id="main">
        <form name="BrowseForm" id="BrowseForm" action="<?php echo $net2ftp_globals["action_url"]; ?>" method="post">
<?php           printLoginInfo(); ?>
            <input type="hidden" name="<?php echo Yii::app()->request->csrfTokenName ?>" value="<?php echo Yii::app()->request->csrfToken ?>" />
            <input type="hidden" name="state"     value="browse" />
            <input type="hidden" name="state2"    value="main" />
            <input type="hidden" name="entry"     value="" />

            <input type="hidden" name="directory" value="<?php echo $directory_html; ?>" style="width: 400px;" title="(accesskey g)" accesskey="g" /> 


<?php if (isset($warning_directory) == true && $warning_directory != "") { ?>
    <div class="warning-box"><div class="warning-text">
    <?php echo $warning_directory; ?>
    </div></div><br />
<?php } ?>

<?php if (isset($warning_consumption) == true && $warning_consumption != "") { ?>
    <div class="warning-box"><div class="warning-text">
    <?php echo $warning_consumption; ?>
    </div></div><br />
<?php } ?>

<?php if (isset($warning_message) == true && $warning_message != "") { ?>
    <div class="warning-box"><div class="warning-text">
    <?php echo $warning_message; ?>
    </div></div><br />
<?php } ?>

<table style="width: 100%; margin: 0px" border="0" cellpadding="2" cellspacing="0">
<tr>
    <td valign="top" style="text-align: <?php echo __("left"); ?>; padding: 0px">
        <input type="button" class="smallbutton" value="<?php echo Yii::t('mc', 'Move') ?>" onclick="submitBrowseForm('<?php echo $directory_js; ?>', '', 'copymovedelete', 'move');" title="<?php echo Yii::t('mc', 'Move the selected entries') ?>" />
        <input type="button" class="smallbutton" value="<?php echo Yii::t('mc', 'Delete') ?>" onclick="submitBrowseForm('<?php echo $directory_js; ?>', '', 'copymovedelete', 'delete');" title="<?php echo Yii::t('mc', 'Delete the selected entries') ?>" />
        <input type="button" class="smallbutton" value="<?php echo Yii::t('mc', 'Rename') ?>" onclick="submitBrowseForm('<?php echo $directory_js; ?>', '', 'rename', '');" title="<?php echo Yii::t('mc', 'Rename the selected entries') ?>" />
        <div style="margin-top: 3px;">
<?php               if ($net2ftp_settings["functionuse_downloadzip"]    == "yes") { ?><input type="button" class="smallbutton" value="<?php echo Yii::t('mc', 'Download') ?>" onclick="submitBrowseForm('<?php echo $directory_js; ?>', '', 'downloadzip', '');" title="<?php echo Yii::t('mc', 'Download a zip file containing all selected entries') ?>" /> <?php } // end if ?>
        </div>
    </td>
    <td valign="top" style="text-align: <?php echo __("right"); ?>; padding: 0px">
    </td>
</tr>
</table>

<table align="center" style="width: 100%;" border="0" cellpadding="2" cellspacing="2">
    <tr class="browse_rows_heading">
        <td style="width: 20px;"><a href="javascript:CheckAll(document.BrowseForm);"  title="Click to check or uncheck all rows (accesskey t)" accesskey="t"><?php echo Yii::t('mc', "All"); ?></a></td>
        <td colspan="1" width="20"></td>
        <td colspan="1" width="250"><a href="javascript:<?php echo $sortArray["dirfilename"]["onclick"]; ?>" title="<?php echo $sortArray["dirfilename"]["title"]; ?>"><?php echo $sortArray["dirfilename"]["text"]; ?></a><?php echo $sortArray["dirfilename"]["icon"]; ?></td>
        <td colspan="1"><a href="javascript:<?php echo $sortArray["type"]["onclick"];        ?>" title="<?php echo $sortArray["type"]["title"];        ?>"><?php echo $sortArray["type"]["text"];        ?></a><?php echo $sortArray["type"]["icon"];        ?></td>
        <td colspan="1"><a href="javascript:<?php echo $sortArray["size"]["onclick"];        ?>" title="<?php echo $sortArray["size"]["title"];        ?>"><?php echo $sortArray["size"]["text"];        ?></a><?php echo $sortArray["size"]["icon"];        ?></td>
        <td colspan="1"><a href="javascript:<?php echo $sortArray["mtime"]["onclick"];       ?>" title="<?php echo $sortArray["mtime"]["title"];       ?>"><?php echo $sortArray["mtime"]["text"];       ?></a><?php echo $sortArray["mtime"]["icon"];       ?></td>
        <td colspan="1"></td>
    </tr>

<?php /* ----- Up ----- */ ?>
        <tr class="browse_rows_even">
            <td></td>
            <td title="<?php echo Yii::t('mc', "Go to the parent directory"); ?>" style="cursor: pointer; cursor: hand;" onclick="javascript:submitBrowseForm('<?php echo $updirectory_js; ?>', '', 'browse', 'main');">
<?php               printMime("icon", array("dirorfile" => "d")); ?>
            </td>
            <td colspan="1" title="<?php echo Yii::t('mc', "Go to the parent directory"); ?>" style="cursor: pointer; cursor: hand;" onclick="javascript:submitBrowseForm('<?php echo $updirectory_js; ?>', '', 'browse', 'main');">
                <a href="javascript:submitBrowseForm('<?php echo $updirectory_url; ?>','','browse','main');"><?php echo Yii::t('mc', "Up"); ?> ..</a>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>

<?php /* ----- Directories ----- */ ?>
<?php   if ($list["stats"]["directories"]["total_number"] > 0) { ?>

<?php       for ($i=1; $i<=sizeof($list_directories); $i++) { ?>
<?php 
// ----- Some PHP stuff -----
            $rowcounter++;
            if ($rowcounter % 2 == 1) { $odd_even = "odd"; }
            else                      { $odd_even = "even"; }
            if ($list_directories[$i]["selectable"] == "ok") { 
                $onClick = "submitBrowseForm('" . $list_directories[$i]["newdir_js"] . "','','browse','main');"; 
                $title = Yii::t('mc', "Go to the subdirectory {dir}", array('{dir}'=>$list_directories[$i]["dirfilename_html"])); 
                $style = "cursor: pointer; cursor: hand; width: 20px;"; 
                $href = "<a style=\"white-space: nowrap;\" href=\"javascript:" . $onClick . "\">" . $list_directories[$i]["dirfilename_html"] . "</a>\n";
            }
            else { 
                $onClick = "";
                $title = "";
                $style = "";
                $href = "<span style=\"white-space: nowrap;\">" . $list_directories[$i]["dirfilename_html"] . "</span>"; 
            }
// -------------------------- ?>
            <tr class="browse_rows_<?php echo $odd_even; ?>" id="row<?php echo $rowcounter; ?>">
                <td title="<?php echo Yii::t('mc', "Select the directory {dir}", array('{dir}'=>$list_directories[$i]["newdir_html"])); ?>" style="text-align: center; width: 20px;">
<?php               printDirFileProperties($rowcounter, $list_directories[$i], "checkbox", "onclick=\"setColor_js($rowcounter, 'checkbox');\""); ?>
                </td>
                <td onclick="<?php echo $onClick; ?>" title="<?php echo $title; ?>" style="<?php echo $style; ?>">
<?php                   printMime("icon", $list_directories[$i]); ?>
                </td>
                <td onclick="<?php echo $onClick; ?>" title="<?php echo $title; ?>" style="<?php echo $style; ?>">
                    <?php echo $href; ?>
                </td>
                <td>
<?php                   printMime("type", $list_directories[$i]); ?>
                </td>
                <td><?php echo format_bytes($list_directories[$i]["size"]); ?></td>
                <td><?php echo $list_directories[$i]["mtime"]; ?></td>
                <td colspan="1"></td>
            </tr>
<?php       } // end for ?>
<?php   } // end if ?>

<?php /* ----- Files ----- */ ?>
<?php   if ($list["stats"]["files"]["total_number"]> 0) { ?>

<?php       for ($i=1; $i<=sizeof($list_files); $i++) { ?>
<?php 
// ----- Some PHP stuff -----
            $rowcounter++;
            if ($rowcounter % 2 == 1) { $odd_even = "odd"; }
            else                      { $odd_even = "even"; }
            if ($list_files[$i]["selectable"] == "ok") { 
                $onClick = "submitBrowseForm('" . $directory_js . "','" . $list_files[$i]["dirfilename_js"] . "','downloadfile','');"; 
                $title = Yii::t('mc', "Download the file {file}", array('{file}'=>$list_files[$i]["dirfilename_html"])); 
                $style = "cursor: pointer; cursor: hand; width: 20px;"; 
                $href = "<a style=\"white-space: nowrap;\" href=\"javascript:" . $onClick . "\">" . $list_files[$i]["dirfilename_html"] . "</a>\n";
            }
            else { 
                $onClick = "";
                $title = "";
                $style = "";
                $href = "<span style=\"white-space: nowrap;\">" . $list_files[$i]["dirfilename_html"] . "</span>"; 
            }
// -------------------------- ?>
            <tr class="browse_rows_<?php echo $odd_even; ?>" id="row<?php echo $rowcounter; ?>">
                <td title="<?php echo Yii::t('mc', "Select the file {file}", array('{file}'=>$list_files[$i]["dirfilename_html"])); ?>" style="text-align: center; width: 20px;">
<?php               printDirFileProperties($rowcounter, $list_files[$i], "checkbox", "onclick=\"setColor_js($rowcounter, 'checkbox');\""); ?>
                </td>
                <td onclick="<?php echo $onClick; ?>" title="<?php echo $title; ?>" style="<?php echo $style; ?>">
<?php                   printMime("icon", $list_files[$i]); ?>
                </td>
                <td onclick="<?php echo $onClick; ?>" title="<?php echo $title; ?>" style="<?php echo $style; ?>">
                    <?php echo $href; ?>
                </td>
                <td>
<?php                   printMime("type", $list_files[$i]); ?>
                </td>
                <td><?php echo format_bytes($list_files[$i]["size"]); ?></td>
                <td><?php echo $list_files[$i]["mtime"]; ?></td>
                <td style="text-align: center">
<?php               if ($list_files[$i]["selectable"] == "ok") { ?>
<?php
        if ($net2ftp_settings["functionuse_edit"]   == "yes" && $list_files[$i]["size"] < 512 * 1024)
        {
            ?><a href="javascript:submitBrowseForm('<?php echo $directory_js; ?>', '<?php echo $list_files[$i]["dirfilename_js"]; ?>', 'edit', '');"><?php echo Yii::t('mc', "Edit");    ?></a>
<?php } ?>
<?php               }  ?>
                </td>
            </tr>
<?php       } // end for ?>
<?php   } // end if ?>

<?php /* ----- Symlinks ----- */ ?>
<?php   if ($list["stats"]["symlinks"]["total_number"] > 0) { ?>

<?php       for ($i=1; $i<=sizeof($list_symlinks); $i++) { ?>
<?php 
// ----- Some PHP stuff -----
            $rowcounter++;
            if ($rowcounter % 2 == 1) { $odd_even = "odd"; }
            else                      { $odd_even = "even"; }
            if ($list_symlinks[$i]["selectable"] == "ok") { 
                $onClick = "submitBrowseForm('" . $directory_js . "','" . $list_symlinks[$i]["dirfilename_js"] . "','followsymlink','main');"; 
                $title = Yii::t('mc', "Follow symlink {lnk}", array('{lnk}'=>$list_symlinks[$i]["dirfilename_html"])); 
                $style = "cursor: pointer; cursor: hand; width: 20px;"; 
                $href = "<a style=\"white-space: nowrap;\" href=\"javascript:" . $onClick . "\">" . $list_symlinks[$i]["dirfilename_html"] . "</a>\n";
            }
            else { 
                $onClick = "";
                $title = "";
                $style = "";
                $href = "<span style=\"white-space: nowrap;\">" . $list_symlinks[$i]["dirfilename_html"] . "</span>"; 
            }
// -------------------------- ?>
            <tr class="browse_rows_<?php echo $odd_even; ?>" id="row<?php echo $rowcounter; ?>" >
                <td title="<?php echo Yii::t('mc', "Select the symlink {lnk}", array('{lnk}'=>$list_symlinks[$i]["dirfilename_html"])); ?>" style="text-align: center; width: 20px;">
<?php               printDirFileProperties($rowcounter, $list_symlinks[$i], "checkbox", "onclick=\"setColor_js($rowcounter, 'checkbox');\""); ?>
                </td>
                <td onclick="<?php echo $onClick; ?>" title="<?php echo $title; ?>" style="<?php echo $style; ?>">
<?php                   printMime("icon", $list_symlinks[$i]); ?>
                </td>
                <td onclick="<?php echo $onClick; ?>" title="<?php echo $title; ?>" style="<?php echo $style; ?>">
                    <?php echo $href; ?>
                </td>
                <td>
<?php                   printMime("type", $list_symlinks[$i]); ?>
                </td>
                <td><?php echo $list_symlinks[$i]["size"]; ?></td>
                <td><?php echo $list_symlinks[$i]["mtime"]; ?></td>
<?php               if ($net2ftp_settings["functionuse_edit"]   == "yes") { ?><td></td><?php } ?>
            </tr>
<?php       } // end for ?>
<?php   } // end if ?>

<?php /* ----- Unrecognized ----- */ ?>
<?php   if ($list["stats"]["unrecognized"]["total_number"] > 0) { ?>

<?php       for ($i=1; $i<=sizeof($list_unrecognized); $i++) { ?>
<?php           $rowcounter++; ?>
<?php           if ($rowcounter % 2 == 1) { $odd_even = "odd"; }
            else                      { $odd_even = "even"; } ?>
            <tr class="browse_rows_<?php echo $odd_even; ?>" id="row<?php echo $i; ?>" colspan="7">
                <td><?php echo $list_unrecognized[$i]["dirfilename_html"]; ?></td>
            </tr>
<?php       } // end for ?>
<?php   } // end if ?>

<?php /* ----- Empty folder ----- */ ?>
<?php   if ($rowcounter == 0) { ?>
        <tr class="browse_rows_odd">
            <td style="text-align: center;" colspan="7">
                <br /><?php echo Yii::t('mc', "This folder is empty"); ?><br /><br />
            </td>
        </tr>
<?php   } // end if ?>

</table>
</form>

<?php /* ----- Statistics ----- */ ?>
<div style="font-size: 90%; margin-top: 10px; float:left">
<?php echo $list["stats"]["directories"]["total_number"].' '.Yii::t('mc', 'Directories').', '
    .$list["stats"]["files"]["total_number"].' '.Yii::t('mc', 'Files').' ('.$list["stats"]["files"]["total_size_formated"].')' ?>
</div>

    </div>
<?php require_once($net2ftp_globals["application_skinsdir"] . "/" . $net2ftp_globals["skin"] . "/footer.template.php"); ?>
<!-- Template /skins/mc/browse_main_details.template.php end -->
