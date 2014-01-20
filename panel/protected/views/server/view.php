<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->breadcrumbs=array(
    Yii::t('mc', 'Servers')=>array('index'),
    $model->isNewRecord ? Yii::t('mc', 'New Server') : ($my ? 'Server' : CHtml::encode($model->name)),
);

Yii::app()->getClientScript()->registerCoreScript('jquery');
echo CHtml::css('
.adv { display: none; }

#advanced { display: none; }
#files { display: none; }

#buttons input
{
    width: auto;
    margin-left: -1px;
    margin-right: 4px;
    padding: 2px;
    padding-left: 5px;
    padding-right: 5px;
}
');

if (!$model->isNewRecord)
{
$schedule = ($manageUsers && (Yii::app()->user->isSuperuser() || $settings->user_schedule));
$mysql = $editConfigs && @strlen($model->mysqlHost) && ((Yii::app()->params['user_mysql'] && $settings->user_mysql)
    || Yii::app()->user->isSuperuser());
$bgPlugins = Yii::app()->params['use_bukget'];

$this->menu=array(
    array(
        'label'=>Yii::t('mc', 'Chat'),
        'url'=>array('chat', 'id'=>$model->id),
        'visible'=>$chat,
        'icon'=>'chat'
    ),
    array(
        'label'=>$command ? Yii::t('mc', 'Console') : Yii::t('mc', 'Log'),
        'url'=>array('log', 'id'=>$model->id),
        'visible'=>$viewLog,
        'icon'=>'console'
    ),
    array(
        'label'=>Yii::t('mc', 'Players'),
        'url'=>array('/player/index', 'sv'=>$model->id),
        'visible'=>$manageUsers, 'icon'=>'player'
    ),
    array(
        'label'=>Yii::t('mc', 'Files'),
        'url'=>'javascript:showSub("files")',
        'icon'=>'closed',
        'linkOptions'=>array('id'=>'files_main'),
        'submenuOptions'=>array('id'=>'files'),
        'visible'=>$editConfigs || $bgPlugins || $plugins || $manageUsers || $backup,
        'items'=>array(
            array(
                'label'=>Yii::t('mc', 'Config Files'),
                'url'=>array('configs', 'id'=>$model->id),
                'visible'=>$editConfigs,
                'icon'=>'config'
            ),
            array(
                'label'=>Yii::t('mc', 'BukGet Plugins'),
                'url'=>array('bgPlugins', 'id'=>$model->id),
                'visible'=>$bgPlugins ? true : false,
                'icon'=>'plugin'
            ),
            array(
                'label'=>Yii::t('mc', 'Local Plugins'),
                'url'=>array('plugins', 'id'=>$model->id),
                'visible'=>$plugins,
                'icon'=>'plugin'
            ),
            array(
                'label'=>Yii::t('mc', 'FTP File Access'),
                'url'=>array((Yii::app()->params['ftp_client_disabled'] !== true) ? '/ftpClient/index'
                    : 'ftp', 'id'=>$model->id),
                'visible'=>$manageUsers,
                'icon'=>'file'
            ),
            array(
                'label'=>Yii::t('mc', 'Backup'),
                'url'=>array('backup', 'id'=>$model->id),
                'visible'=>$backup,
                'icon'=>'backup'
            ),
        )
    ),
    array(
        'label'=>Yii::t('mc', 'Advanced'),
        'url'=>'javascript:showSub("advanced")',
        'icon'=>'closed',
        'linkOptions'=>array('id'=>'advanced_main'),
        'submenuOptions'=>array('id'=>'advanced'),
        'visible'=>$manageUsers || $schedule || $mysql,
        'items'=>array(
            array(
                'label'=>Yii::t('mc', 'Commands'),
                'url'=>array('/command/index', 'sv'=>$model->id),
                'visible'=>$manageUsers,
                'icon'=>'command'
            ),
            array(
                'label'=>Yii::t('mc', 'Scheduled Tasks'),
                'url'=>array('/schedule/index', 'sv'=>$model->id),
                'visible'=>$schedule,
                'icon'=>'schedule'),
            array(
                'label'=>Yii::t('mc', 'Users'),
                'url'=>array('users', 'id'=>$model->id),
                'visible'=>$manageUsers,
                'icon'=>'user'
            ),
            array(
                'label'=>Yii::t('mc', 'MySQL Database'),
                'url'=>array('mysqlDb', 'id'=>$model->id),
                'visible'=>$mysql,
                'icon'=>'mysql'
            ),
        )
    ),
    array(
        'label'=>Yii::t('mc', 'Delete Server'),
        'url'=>array('delete', 'id'=>$model->id),
        'visible'=>$delete,
        'icon'=>'delete'
    ),
);
}
else
    $this->menu = array(array('label'=>Yii::t('mc', 'Back'), 'url'=>array('index'), 'icon'=>'back'));

echo CHtml::script('
    imgOpen = "'.Theme::themeFile('images/icons/open.png').'";
    imgClosed = "'.Theme::themeFile('images/icons/closed.png').'";
    menuShown = {}
    function showSub(name)
    {
        menuShown[name] = !menuShown[name];
        $("#"+name+"_main").children("img").attr("src", !menuShown[name] ? imgClosed : imgOpen);
        $("#"+name).stop(true, true).slideToggle(menuShown[name]);
    }
');
?>

<?php if (Yii::app()->user->isSuperuser()): ?>
    <div id="movestatus-ajax">
        <?php echo @$data['movestatus'] ?>
    </div>
<?php endif ?>

<?php 
    $statusIcon = Yii::t('mc', 'Server Settings');
    $statusButtons = '';
    if (isset($data)):
    ob_start(); ?>
    <div style="float: left; width: 30px; margin-top: 3px" id="statusicon-ajax">
        <?php echo $data['statusicon'] ?>
    </div>
    <?php
    $statusIcon = ob_get_clean();
    ob_start();
    ?>
    <div id="buttons">
<?php
    echo CHtml::ajaxButton(Yii::t('mc', 'Start'), '', array(
            'type'=>'POST', 'data'=>array('ajax'=>'start', Yii::app()->request->csrfTokenName=>Yii::app()->request->csrfToken,),
            'success'=>'function(e) {if (e) alert(e);}'
         ),
        $data['buttons'][0] != '1' ? array() : array('disabled'=>'disbled'));
    echo CHtml::ajaxButton(Yii::t('mc', 'Stop'), '', array(
            'type'=>'POST', 'data'=>array('ajax'=>'stop', Yii::app()->request->csrfTokenName=>Yii::app()->request->csrfToken,),
            'success'=>'function(e) {if (e) alert(e);}'
        ),
        $data['buttons'][1] != '1' ? array() : array('disabled'=>'disbled'));
    echo CHtml::ajaxButton(Yii::t('mc', 'Restart'), '', array(
            'type'=>'POST', 'data'=>array('ajax'=>'restart', Yii::app()->request->csrfTokenName=>Yii::app()->request->csrfToken,),
            'success'=>'function(e) {if (e) alert(e);}'
        ),
        $data['buttons'][2] != '1' ? array() : array('disabled'=>'disbled'));
?>
    </div>
    <div id="buttons-ajax" style="display: none">
        <?php echo $data['buttons'] ?>
    </div>
<?php
    $statusButtons = ob_get_clean(); 
    endif ?>

<?php
if (@$my)
{
    $url = CHtml::normalizeUrl(array('view', 'id'=>'__SERVER_ID__'));
    echo CHtml::script('
        function changeServer()
        {
            window.location.href="'.str_replace('__SERVER_ID__', '" + $("#my_servers").val() + "', $url).'";
        }
    ');
    echo '<div>'.CHtml::dropDownList('my_servers', $model->id, $my, array('onchange'=>'changeServer()')).'</div><br/>';
}
?>

<?php 

$statusDetail = false;
if (@$data['statusdetail'])
    $statusDetail = array('label'=>Yii::t('mc', 'Status'), 'type'=>'raw', 'value'=>'<nobr><div id="statusdetail-ajax">'.CHtml::encode($data['statusdetail']).'</div></nobr>');

$statusBanner = false;
if (!!Yii::app()->params['status_banner'] && !$model->isNewRecord)
{
    $bannerUrl = CHtml::normalizeUrl(array('status/'.$model->id.'.png'));
    $statusBanner = array('label'=>Yii::t('mc', 'Status Banner'), 'type'=>'raw',
        'value'=>CHtml::link(Yii::t('mc', 'Show'), $bannerUrl));
}

$ip = trim(($settings && $settings->display_ip) ? $settings->display_ip : $model->ip);
if (!strlen($ip) || $ip == '0.0.0.0')
{
    if ($dmn = Daemon::model()->findByPk($model->daemon_id))
        $ip = $dmn->ip;
}
$attribs = array();
$attribs[] = array('label'=>$statusIcon, 'type'=>'raw', 'value'=>$statusButtons, 'cssClass'=>'titlerow', 'template'=>"<tr class=\"{class}\"><th>{label}</th><td colspan=\"2\">{value}</td></tr>\n");
if (!$edit)
{
    if ($ip && $ip != '0.0.0.0')
        $attribs[] = array('label'=>CHtml::activeLabel($model, 'ip'), 'type'=>'raw', 'value'=>'<nobr>'.CHtml::encode($ip).'</nobr>');
    $attribs[] =  'port';
    if ($statusDetail)
        $attribs[] = $statusDetail;
    if ($statusBanner)
        $attribs[] = $statusBanner;
}
else
{
    //possible values for default_role
    $defaultRoles = array_combine(User::$roleLevels, User::getRoleLabels());
    array_pop($defaultRoles);//remove owner
    array_pop($defaultRoles);//remove admin
    //possible values for permission roles
    $allRoles = array_combine(User::$roles, User::getRoleLabels());
    //possible values for ip authentication
    $ipRoles = $allRoles;
    array_pop($ipRoles);//remove owner
    array_pop($ipRoles);//remove admin
    $form=$this->beginWidget('CActiveForm', array(
            'id'=>'server-form',
            'enableAjaxValidation'=>false,
        ));

    $conIds = McBridge::get()->getDaemonIds();
    $conCount = count($conIds);
    if (($conCount > 1 || ($conCount == 1 && $model->daemon_id != $conIds[0])) && Yii::app()->user->isSuperuser())
    {
        $opt = array();
        
        $attribs[] = array('label'=>$form->labelEx($model,'daemon_id'), 'type'=>'raw',
            'value'=>$form->dropDownList($model, 'daemon_id', McBridge::get()->conStrings(), $opt)
                .' '.$form->error($model,'daemon_id'),
            'hint'=>Yii::t('mc', 'Changing this will shut the server down if running'));
        echo CHtml::hiddenField('move_files', '');
    }
    else if ($model->isNewRecord && $conCount == 1)
    {
        $model->daemon_id = $conIds[0];
        echo $form->hiddenField($model, 'daemon_id');
    }
    if (Yii::app()->user->isSuperuser() || $settings->user_name)
    {
        $attribs[] = array('label'=>$form->labelEx($model,'name'), 'type'=>'raw',
            'value'=>$form->textField($model,'name').' '.$form->error($model,'name'));
    }
    else
        $attribs[] = 'name';
    if (Yii::app()->user->isSuperuser() || $settings->user_players)
    {
        $attribs[] = array('label'=>$form->labelEx($model,'players'), 'type'=>'raw',
            'value'=>$form->textField($model,'players').' '.$form->error($model,'players'));
    }
    else
        $attribs[] = 'players';
    if ($statusDetail)
        $attribs[] = $statusDetail;
    if ($statusBanner)
        $attribs[] = $statusBanner;
    if (Yii::app()->user->isSuperuser())
    {
        if (!$model->isNewRecord)
        {
            $attribs[] = array('label'=>$form->labelEx($model,'suspended'), 'type'=>'raw',
                'value'=>($model->suspended ? Yii::t('mc', 'Yes') : Yii::t('mc', 'No'))
                    .' ('.CHtml::link($model->suspended ? Yii::t('mc', 'Resume') : Yii::t('mc', 'Suspend'),
                        array('server/'.($model->suspended ? 'resume' : 'suspend'), 'id'=>$model->id))
                    .') '.$form->error($model,'suspended'));
        }        
        $attribs[] = array('label'=>Yii::t('mc', 'Assign to user'), 'type'=>'raw',
            'value'=>CHtml::dropDownList('user-assign', $model->owner,
                array('0'=>Yii::t('mc', 'None')) + CHtml::listData(User::model()->findAll(array('order'=>'name asc')), 'id', 'name')),
            'hint'=>($model->isNewRecord ? '' :
                    Yii::t('mc', 'Remove role and FTP access of old owner manually')));
        if (Yii::app()->params['mail_assign'])
        {
            $attribs[] = array('label'=>Yii::t('mc', 'Send Assign Notification'), 'type'=>'raw',
                'value'=>CHtml::checkBox('send_data', true));
        }
        $attribs[] = array('label'=>$form->labelEx($model,'ip'), 'type'=>'raw',
            'value'=>$form->textField($model,'ip')
                .' '.$form->error($model,'ip'),
            'hint'=>Yii::t('mc', 'Empty for default value'));
        $attribs[] = array('label'=>$form->labelEx($settings,'display_ip'), 'type'=>'raw',
            'value'=>$form->textField($settings,'display_ip')
                .' '.$form->error($settings,'display_ip'),
            'hint'=>Yii::t('mc', 'Displayed on banner and in server view. Empty for same as IP'));
        $attribs[] = array('label'=>$form->labelEx($model,'port'), 'type'=>'raw',
            'value'=>$form->textField($model,'port')
                .' '.$form->error($model,'port'),
            'hint'=>Yii::t('mc', 'Empty to select automatically'));
        $attribs[] = array('label'=>$form->labelEx($model,'memory'), 'type'=>'raw',
            'value'=>$form->textField($model,'memory')
                .' '.$form->error($model,'memory'),
            'hint'=>Yii::t('mc', 'In MB. Empty for default amount'));
    }
    else
    {
        if ($ip && $ip != '0.0.0.0')
            $attribs[] = array('label'=>CHtml::activeLabel($model, 'ip'), 'value'=>$ip);
        $attribs[] = 'port';
        $attribs[] = array('label'=>$form->labelEx($model,'world'), 'type'=>'raw',
            'value'=>$form->textField($model,'world').' '.$form->error($model,'world'),
            'hint'=>Yii::t('mc', 'Leave empty for "world"'));
    }
    if (Yii::app()->user->isSuperuser() || ($settings->user_jar && in_array($model->jardir, array('server', 'server_base'))))
    {
        if ($jars)
        {
            $attribs[] = array('label'=>'', 'type'=>'raw',
                'value'=>CHtml::dropDownList('jar-select', $model->jarfile, $jars),
                'hint'=>Yii::t('mc', 'JAR file selection'));
        }
        $attribs[] = array('label'=>$form->labelEx($model,'jarfile'), 'type'=>'raw',
            'value'=>$form->textField($model,'jarfile').' '.$form->error($model,'jarfile'),
            'hint'=>Yii::t('mc', 'Empty for default file.'));
    }
    else if ($jars && $settings->user_jar)
    {
        $attribs[] = array('label'=>Yii::t('mc', 'Server JAR'), 'type'=>'raw',
            'value'=>CHtml::dropDownList('jar-select', $model->jarfile, $jars));
    }
    if (Yii::app()->user->isSuperuser())
    {
        $attribs[] = array('label'=>$form->labelEx($settings,'user_jar'), 'type'=>'raw',
            'value'=>$form->checkBox($settings,'user_jar')
                .' '.$form->error($settings,'user_jar'));
        $attribs[] = array('label'=>$form->labelEx($settings,'user_name'), 'type'=>'raw',
            'value'=>$form->checkBox($settings,'user_name')
                .' '.$form->error($settings,'user_name'));
    }
    $attribs[] = array('label'=>Theme::img('icons/closed.png', '', array('id'=>'advImg')), 'type'=>'raw',
            'value'=>CHtml::link(Yii::t('mc', 'Show Advanced Options'), '#', array('id'=>'advTxt', 'onclick'=>'return checkAdv()')));
    if (Yii::app()->user->isSuperuser())
    {
        $attribs[] = array('label'=>$form->labelEx($model,'world'), 'type'=>'raw', 'cssClass'=>'adv',
            'value'=>$form->textField($model,'world').' '.$form->error($model,'world'),
            'hint'=>Yii::t('mc', 'Leave empty for "world"'));
        $attribs[] = array('label'=>$form->labelEx($model,'dir'), 'type'=>'raw', 'cssClass'=>'adv',
            'value'=>$form->textField($model,'dir')
                .' '.$form->error($model,'dir'),
            'hint'=>Yii::t('mc', 'Contains all files for this server'));
        $attribs[] = array('label'=>$form->labelEx($model,'start_memory'), 'type'=>'raw', 'cssClass'=>'adv',
            'value'=>$form->textField($model,'start_memory')
                .' '.$form->error($model,'start_memory'),
            'hint'=>Yii::t('mc', 'In MB. Empty for same as Max. Memory'));
        $attribs[] = array('label'=>$form->labelEx($model,'autostart'), 'type'=>'raw', 'cssClass'=>'adv',
            'value'=>$form->checkBox($model,'autostart')
                .' '.$form->error($model,'autostart'),
            'hint'=>Yii::t('mc', 'Start this server automatically when Multicraft restarts'));
        $attribs[] = array('label'=>$form->labelEx($settings,'user_schedule'), 'type'=>'raw', 'cssClass'=>'adv',
            'value'=>$form->checkBox($settings,'user_schedule')
                .' '.$form->error($settings,'user_schedule'),
            'hint'=>Yii::t('mc', 'Owner can create scheduled tasks and change the autosave setting'));
        $attribs[] = array('label'=>$form->labelEx($settings,'user_ftp'), 'type'=>'raw', 'cssClass'=>'adv',
            'value'=>$form->checkBox($settings,'user_ftp')
                .' '.$form->error($settings,'user_ftp'),
            'hint'=>Yii::t('mc', 'Owner can give FTP access to other users'));
        $attribs[] = array('label'=>$form->labelEx($settings,'user_visibility'), 'type'=>'raw', 'cssClass'=>'adv',
            'value'=>$form->checkBox($settings,'user_visibility')
                .' '.$form->error($settings,'user_visibility'),
            'hint'=>Yii::t('mc', 'Owner can change the server visibility and Default Role'));
        $attribs[] = array('label'=>$form->labelEx($settings,'user_players'), 'type'=>'raw', 'cssClass'=>'adv',
            'value'=>$form->checkBox($settings,'user_players')
                .' '.$form->error($settings,'user_players'));
        if (Yii::app()->params['user_mysql'] )
        {
            $attribs[] = array('label'=>$form->labelEx($settings,'user_mysql'), 'type'=>'raw', 'cssClass'=>'adv',
                'value'=>$form->checkBox($settings,'user_mysql')
                    .' '.$form->error($settings,'user_mysql'));
        }
        $attribs[] = array('label'=>$form->labelEx($model,'jardir'), 'type'=>'raw', 'cssClass'=>'adv',
            'value'=>$form->dropDownList($model,'jardir',Server::getJardirs())
                .' '.$form->error($model,'jardir'),
            'hint'=>Yii::t('mc', '(* Warning: Be sure to run Multicraft in "multiuser" mode with this!)'));
    }
    else if (Yii::app()->params['show_memory'])
        $attribs[] = array('label'=>$form->labelEx($model,'memory'), 'value'=>$model->memory.' '.Yii::t('mc', 'MB'),
            'cssClass'=>'adv');
    $attribs[] = array('label'=>$form->labelEx($model,'kick_delay'), 'type'=>'raw', 'cssClass'=>'adv',
        'value'=>$form->textField($model,'kick_delay')
            .' '.$form->error($model,'kick_delay'),
        'hint'=>Yii::t('mc', 'After how many milliseconds to kick players without access'));
    if (Yii::app()->user->isSuperuser() || $settings->user_schedule)
    {
        $attribs[] = array('label'=>$form->labelEx($model,'autosave'), 'type'=>'raw', 'cssClass'=>'adv',
            'value'=>$form->checkBox($model,'autosave')
            .' '.$form->error($model,'autosave'),
            'hint'=>Yii::t('mc', 'Regularly save the world to the disk'));
    }
    $attribs[] = array('label'=>$form->labelEx($model,'announce_save'), 'type'=>'raw', 'cssClass'=>'adv',
        'value'=>$form->checkBox($model,'announce_save')
            .' '.$form->error($model,'announce_save'),
        'hint'=>Yii::t('mc', 'Inform the players when the world has been saved'));
    if (Yii::app()->user->isSuperuser() || ($edit && Yii::app()->params['user_chunkster']))
    {
        $attribs[] = array('label'=>Yii::t('mc', 'Chunkster'), 'type'=>'raw', 'cssClass'=>'adv',
            'value'=>CHtml::submitButton(Yii::t('mc', 'Run Chunkster'), array('name'=>'chunkster',
                'confirm'=>Yii::t('mc', 'This tool will only run when your server has been fully stopped first. Use it at your own risk.'),
                'submit'=>'', 'params'=>array('run_chunkster'=>'true'))),
            'hint'=>Yii::t('mc', 'Chunkster is a tool that can fix corrupted worlds. Use it at your own risk and create a backup first.'));
    }

$this->widget('zii.widgets.CDetailView', array(
    'data'=>$model,
    'attributes'=>$attribs,
));
$attribs = array();

$attribs[] = array('label'=>Yii::t('mc', 'Permissions'), 'value'=>'', 'cssClass'=>'titlerow');
    if (Yii::app()->user->isSuperuser() || $settings->user_visibility)
    {
        $attribs[] = array('label'=>$form->labelEx($settings,'visible'), 'type'=>'raw',
            'value'=>$form->dropDownList($settings,'visible',ServerConfig::getVisibility())
                .' '.$form->error($settings,'visible'),
            'hint'=>Yii::t('mc', 'Visibility in the Multicraft server list'));
        $attribs[] = array('label'=>$form->labelEx($model,'default_level'), 'type'=>'raw',
            'value'=>$form->dropDownList($model,'default_level',$defaultRoles)
                .' '.$form->error($model,'default_level'),
            'hint'=>Yii::t('mc', 'Role assigned to players on first connect ("No Access" for whitelisting)'));
    }
    $attribs[] = array('label'=>$form->labelEx($settings,'ip_auth_role'), 'type'=>'raw',
        'value'=>$form->dropDownList($settings,'ip_auth_role', $ipRoles)
            .' '.$form->error($settings,'ip_auth_role'),
        'hint'=>Yii::t('mc', 'For users whose IP matches a player ingame'));
    $attribs[] = array('label'=>CHtml::label(Yii::t('mc', 'Cheat Role'),'cheat_role'), 'type'=>'raw',
        'value'=>CHtml::dropDownList('cheat_role', $settings->give_role, $ipRoles),
        'hint'=>Yii::t('mc', 'Role required to use web based give/teleport'));

    $attribs[] = array('label'=>'', 'type'=>'raw', 'value'=>CHtml::submitButton($model->isNewRecord ? Yii::t('mc', 'Create') : Yii::t('mc', 'Save')));

    if (isset($data['resources']))
    {
        $set = Setting::model()->findByPk('resourceCheckInterval');
        if (!$set || $set->value > 0)
        {
            echo '<div id="resources-ajax">'.$data['resources'].'</div>';
        }
    }
?>
<br/>

<?php
}

?>
<?php
$this->widget('zii.widgets.CDetailView', array(
    'data'=>$model,
    'attributes'=>$attribs,
));

if ($edit)
    $this->endWidget();
?>

<?php if(Yii::app()->user->hasFlash('server')): ?>
<div class="flash-success">
    <?php echo Yii::app()->user->getFlash('server'); ?>
</div>
<?php endif ?>


<?php if (!$model->isNewRecord): ?>
<br/>

<table style="width: 100%" class="stdtable">
<tr class="titlerow"> 
    <td><?php echo Yii::t('mc', 'Connected players') ?></td>
</tr>
<tr class="linerow">
    <td></td>
</tr>
<tr>
    <td>
        <?php if ($getPlayers): ?>
        <!-- PLAYERS -->
        <table class="stdtable">
        <tbody id="players-ajax">
        <?php echo $data['players'] ?>
        </tbody>
        </table>
        <?php endif ?>
    </td>
</tr>
</table>

<?php
    echo CHtml::script('
        function buttonChange() {
            dis = $("#buttons-ajax").html();
            for (i = 0; i < 3; i++)
            {
                if (dis[i] != "1")
                    $("input[name=yt" + i + "]").removeAttr("disabled");
                else
                    $("input[name=yt" + i + "]").attr("disabled", "disabled");
            }
        }
    ');
    $this->printRefreshScript('buttonChange'); ?>

<?php endif ?>

<?php
if (Yii::app()->user->isSuperuser() || ($settings->user_jar && in_array($model->jardir, array('server', 'server_base'))))
{
    echo CHtml::script('
        $("#jar-select").change(function() {
            $("#Server_jarfile").val($(this).children("option:selected").val());
        });
    ');
}
if (!$model->isNewRecord && Yii::app()->user->isSuperuser())
{
    echo CHtml::script('
$("#server-form").submit(function() {
    var initDid = '.$model->daemon_id.';
    var dmnSel = $("#Server_daemon_id") ? $("#Server_daemon_id").children("option:selected").val() : null;
    if (dmnSel && dmnSel != initDid)
    {
        var move = confirm("'.Yii::t('mc', 'Press OK to move the server and all server files to the new daemon. This process can take a lot of time depending on the total number and size of all server files.\n\nThe daemon dropdown will revert back to the previous one until the transfer is complete.\n\nPressing cancel will only change the daemon ID setting without moving any files.').'");
        if (move)
        {
            $("#move_files").val(dmnSel);
            $("#Server_daemon_id").val(initDid);
            }
        }
        return true;
    });
    ');
}
echo CHtml::script('
    advShow = false;
    txtOpen = "'.Yii::t('mc', 'Hide Advanced Options').'";
    txtClosed = "'.Yii::t('mc', 'Show Advanced Options').'";
    function checkAdv()
    {
        advShow = !advShow;
        $("#advImg").attr("src", advShow ? imgOpen : imgClosed);
        $("#advTxt").html(advShow ? txtOpen : txtClosed);
        $(".adv").toggle(advShow);
        return false;
    }
    $("#adv_opts").change(function() { checkAdv(); });
    '.(@$advanced ? '$(function() { checkAdv(); });' : '').'
');
?>
