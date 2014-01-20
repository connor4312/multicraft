<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

$this->pageTitle=Yii::app()->name . ' - '.Yii::t('admin', 'Panel Configuration');
$this->breadcrumbs=array(
    Yii::t('admin', 'Settings')=>array('index'),
    Yii::t('admin', 'Panel Configuration'),
);

$this->menu=array(
    array(
        'label'=>Yii::t('admin', 'Back'),
        'url'=>array('daemon/index'),
        'icon'=>'back',
    ),
);

echo CHtml::css('.adv { display: none; }');
echo CHtml::beginForm();
echo CHtml::hiddenField('submit_settings', 'true');

if(Yii::app()->user->hasFlash('panel_config')): ?>
<div class="flash-error">
    <?php echo Yii::app()->user->getFlash('panel_config'); ?>
</div>
<?php endif;

$attr = array();

function set_t($p, $id, $desc, $hint = '', $advanced = false, $list = false)
{
    $val = '';
    if (!$list)
        $val = CHtml::textField('settings['.$id.']', @$p['config'][$id]);
    else
        $val = CHtml::dropDownList('settings['.$id.']', @$p['config'][$id], $list);
    return array('label'=>$desc, 'type'=>'raw', 'value'=>$val,
        'hint'=>$hint, 'cssClass'=>(!$advanced ? '' : (is_string($advanced) ? $advanced : 'adv')));
}
function set_s($p, $id, $desc, $hint = '', $advanced = false)
{
    return array('label'=>$desc, 'type'=>'raw', 'value'=>CHtml::dropDownList('settings['.$id.']',
            @$p['config'][$id] ? 'sel_true' : 'sel_false', array('sel_true'=>Yii::t('admin', 'True'), 'sel_false'=>Yii::t('admin', 'False'))),
        'hint'=>$hint, 'cssClass'=>(!$advanced ? '' : (is_string($advanced) ? $advanced : 'adv')));
}

$attr[] = set_t($p, 'admin_name', Yii::t('admin', 'Administrator Name'), '');
$attr[] = set_t($p, 'admin_email', Yii::t('admin', 'Administrator contact Email'), Yii::t('admin', 'empty to hide the "Support" menu entry'));
$attr[] = set_t($p, 'admin_ips', Yii::t('admin', 'Restrict Administrator IPs'), Yii::t('admin', 'Only allow these IPs to login as a superuser, empty for no restriction.'));
$attr[] = set_t($p, 'api_ips', Yii::t('admin', 'Restrict API IPs'), Yii::t('admin', 'Only allow these IPs to use the API, empty for no restriction.'));
$attr[] = set_s($p, 'api_enabled', Yii::t('admin', 'Enable the Multicraft API'));
$attr[] = set_s($p, 'hide_userlist', Yii::t('admin', 'Hide the userlist from normal users'));
$attr[] = set_t($p, 'min_pw_length', Yii::t('admin', 'Minimum password length'));
$attr[] = set_t($p, 'theme', Yii::t('admin', 'Theme'), '', false, Controller::themeSelection());
$attr[] = set_t($p, 'language', Yii::t('admin','Language'), '', false, Controller::languageSelection());
$attr[] = set_s($p, 'status_banner', Yii::t('admin', 'Generate server status banners (requires GD)'), '');
$attr[] = set_s($p, 'mail_welcome', Yii::t('admin', 'Welcome Mail'), Yii::t('admin', 'Send a welcome email when a new user is created'));
$attr[] = set_s($p, 'mail_assign', Yii::t('admin', 'Assign Mail'), Yii::t('admin', 'Send a notification email when a server is assigned to a user'));
$attr[] = set_t($p, 'default_display_ip', Yii::t('admin', 'Default Display IP'), Yii::t('admin', 'Used when a new server is created'));
$attr[] = set_s($p, 'show_memory', Yii::t('admin', 'Show Server Memory'), Yii::t('admin', 'Display server memory to users in the advanced section'));
$attr[] = set_s($p, 'use_bukget', Yii::t('admin', 'Use BukGet plugin list'), Yii::t('admin', 'See the How-To section on the Multicraft website'));
$attr[] = set_s($p, 'user_mysql', Yii::t('admin', 'Allow Users to Create a MySQL Database'), '', 'mysql_main');
$attr[] = set_t($p, 'user_mysql_host', Yii::t('admin', 'User DB Host'), Yii::t('admin', 'Set to * to use the daemon IP'), 'mysql');
$attr[] = set_t($p, 'user_mysql_user', Yii::t('admin', 'User DB Username'), Yii::t('admin', 'Must have database create privileges'), 'mysql');
$attr[] = set_t($p, 'user_mysql_pass', Yii::t('admin', 'User DB Password'), '', 'mysql');
$attr[] = set_t($p, 'user_mysql_prefix', Yii::t('admin', 'User DB Prefix'), Yii::t('admin', 'The user database is named prefix + server ID'), 'mysql');
$attr[] = set_t($p, 'user_mysql_admin', Yii::t('admin', 'User DB Admin Link'), Yii::t('admin', 'For example a link to phpMyAdmin. "*" will be replaced with the daemon IP'), 'mysql');
$attr[] = array('label'=>Theme::img('icons/closed.png', '', array('id'=>'advImg', 'onclick'=>'return checkAdv()')),
    'type'=>'raw', 'value'=>CHtml::link(Yii::t('admin', 'Show Advanced Options'), '#',
        array('id'=>'advTxt', 'onclick'=>'return checkAdv()')));
$attr[] = set_s($p, 'ftp_client_disabled', Yii::t('admin', 'Disable the integrated FTP client (net2ftp)'), '', true);
$attr[] = set_s($p, 'user_chunkster', Yii::t('admin', 'Allow Users to run the Chunkster tool'), Yii::t('admin', 'This requires more configuration, see the How-To section on the Multicraft website.'), true);
$attr[] = set_s($p, 'ajax_updates_disabled', Yii::t('admin', 'Disable AJAX updates'), Yii::t('admin', 'Reduces HTTP requests but disables console/chat/status autorefresh'), true);
$attr[] = set_s($p, 'ajax_serverlist', Yii::t('admin', 'Use AJAX in the serverlist for faster loading'), Yii::t('admin', 'This will cause a separate HTTP request for each server visible on the list'), true);
$attr[] = set_s($p, 'sqlitecache_schema', Yii::t('admin', 'Use schema cache to reduce queries'), Yii::t('admin', 'Requires the PDO SQLite extension'), true);
$attr[] = set_s($p, 'sqlitecache_commands', Yii::t('admin', 'Use separate DB for command cache'), Yii::t('admin', 'Requires the PDO SQLite extension'), true);
$attr[] = set_t($p, 'timeout', Yii::t('admin', 'Timeout for client communication'), Yii::t('admin', 'in seconds'), true);
$attr[] = set_s($p, 'superuser_check_only', Yii::t('admin', 'Only check for updates as Superuser'), '', true);
$attr[] = set_s($p, 'register_disabled', Yii::t('admin', 'Disable User Registration'), Yii::t('admin', 'Disables the user registration functionality'), true);
$attr[] = set_t($p, 'login_tries', Yii::t('admin', 'Number of login attempts before blocking'), Yii::t('admin', '0 to disable'), true);
$attr[] = set_t($p, 'login_interval', Yii::t('admin', 'Login block interval'), Yii::t('admin', 'in seconds'), true);
$attr[] = set_t($p, 'reset_token_hours', Yii::t('admin', 'Reset Token Valid for (hours)'), Yii::t('admin', 'The reset token expires after the specified number of hours. Use 0 to disable the password reset functionality.'), true);
$attr[] = set_s($p, 'default_ignore_ip', Yii::t('admin', 'Allow IP change for login sessions by default'), Yii::t('admin', 'Allowing IP changes means that the login session will be considered valid even if a users IP changes. This is useful when logging in on mobile devices but it also means that stolen session cookies will be valid.'), true);
$attr[] = set_s($p, 'api_allow_get', Yii::t('admin', 'Allow GET requests to the API'), '', true);
$attr[] = set_s($p, 'enable_csrf_validation', Yii::t('admin', 'Enable CSRF validation'), '', true);
$attr[] = set_s($p, 'enable_cookie_validation', Yii::t('admin', 'Enable Cookie validation'), '', true);

$attr[] = array('label'=>'', 'type'=>'raw', 'value'=>CHtml::submitButton(Yii::t('admin', 'Save')));

$this->widget('zii.widgets.CDetailView', array(
    'data'=>array(),
    'attributes'=>$attr,
)); 
echo CHtml::endForm();

?>
<br/>
<br/>
<br/>
<br/>
<?php

echo CHtml::script('
    advShow = false;
    imgOpen = "'.Theme::themeFile('images/icons/open.png').'";
    imgClosed = "'.Theme::themeFile('images/icons/closed.png').'";
    txtOpen = "'.Yii::t('admin', 'Hide Advanced Options').'";
    txtClosed = "'.Yii::t('admin', 'Show Advanced Options').'";
    function checkAdv()
    {
        advShow = !advShow;
        $("#advImg").attr("src", advShow ? imgOpen : imgClosed);
        $("#advTxt").html(advShow ? txtOpen : txtClosed);
        $(".adv").toggle(advShow);
        return false;
    }

    function checkMysql(obj)
    {
        $(".mysql").toggle($(obj).val() == "sel_true");
    }

    $(function() {
        '.(@$advanced ? 'checkAdv();' : '').'

        sel = $(".mysql_main").find("select");
        sel.change(function() { checkMysql(this); });
        checkMysql(sel);

    });
');
