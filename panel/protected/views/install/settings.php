<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle=Yii::app()->name . ' - '.Yii::t('mc', 'Multicraft Installer');

?>
<?php 
if (@$p[$type.'_db_connected'] && !Yii::app()->user->isSuperuser())
    return;
echo CHtml::beginForm(array('index', 'step'=>'connection'));
echo CHtml::hiddenField('submit_settings', 'true');
$attr = array();

function set_t($p, $id, $desc, $hint = '', $list = false)
{
    $val = '';
    if (!$list)
        $val = CHtml::textField('settings['.$id.']', @$p['config'][$id]);
    else
        $val = CHtml::dropDownList('settings['.$id.']', @$p['config'][$id], $list);
    return array('label'=>$desc, 'type'=>'raw', 'value'=>$val,
        'hint'=>$hint);
}
function set_s($p, $id, $desc, $hint = '')
{
    return array('label'=>$desc, 'type'=>'raw', 'value'=>CHtml::dropDownList('settings['.$id.']',
            @$p['config'][$id] ? 'sel_true' : 'sel_false', array('sel_true'=>'True', 'sel_false'=>'False')),
        'hint'=>$hint);
}

echo 'Basic configuration. More settings will be available in the panel under Settings-&gt;Panel Configuration.<br/><br/>';
$attr[] = array('label'=>'', 'type'=>'raw', 'value'=>CHtml::submitButton('Save'));

$attr[] = set_t($p, 'admin_email', 'Administrator contact Email', 'empty to hide the "Support" menu entry');
$attr[] = set_s($p, 'api_enabled', 'Enable the Multicraft API');
$attr[] = set_t($p, 'api_ips', Yii::t('admin', 'Restrict API IPs'), Yii::t('admin', 'Only allow these IPs to use the API, empty for no restriction.'));
$attr[] = set_s($p, 'hide_userlist', 'Hide the userlist from normal users');
$attr[] = set_s($p, 'ftp_client_disabled', 'Disable the integrated FTP client (net2ftp)');
$attr[] = set_t($p, 'theme', 'Theme', '', Controller::themeSelection());
$attr[] = set_t($p, 'language', 'Language', '', Controller::languageSelection());
if (@$p['config']['superuser'] != 'admin')
    $attr[] = set_t($p, 'superuser', 'Root Superuser', 'Please create a user named "admin" and then set this to "admin"');
$attr[] = set_t($p, 'daemon_password', 'Password for daemon connections', 'Must be the same as "<b>password</b>" in your "<b>multicraft.conf</b>"');
$attr[] = set_t($p, 'login_tries', 'Number of login attempts before blocking', '0 to disable');
$attr[] = set_s($p, 'status_banner', 'Generate server status banners (requires GD)', '');

$attr[] = array('label'=>'', 'type'=>'raw', 'value'=>CHtml::submitButton('Save'));

$this->widget('zii.widgets.CDetailView', array(
    'data'=>array(),
    'attributes'=>$attr,
)); 
echo CHtml::endForm();


?>
