<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle=Yii::app()->name . ' - '.Yii::t('admin', 'Multicraft Operations');
$this->breadcrumbs=array(
    Yii::t('admin', 'Settings')=>array('index'),
    Yii::t('admin', 'Operations'),
);

$this->menu=array(
    array(
        'label'=>Yii::t('admin', 'Back'),
        'url'=>array('daemon/index'),
        'icon'=>'back',
    ),
);
?>

<?php 

global $broadcastMsg;
$broadcastMsg = Yii::t('admin', "Warning: This will affect all servers running on the selected daemon(s)!\n\nSelected action: ");
$playersCleanupMsg = Yii::t('admin', 'Warning: This will delete all players that haven\'t been explicitly assigned a role. This will be the vast majority of player entries and the "last seen", "quit reason" and "IPs" information for these players will be lost. Proceed?');
$cmdcacheCleanupMsg = Yii::t('admin', 'This clears the table that caches daemon queries, Proceed?');

function addButton($label, $name, $msg = false)
{
    global $broadcastMsg;
    return CHtml::submitButton($label, array('name'=>$name,
        'confirm'=>($msg ? $msg : $broadcastMsg.$label)));
}
function beginForm($daemon_id)
{
    return CHtml::beginForm().CHtml::hiddenField('daemon_id', $daemon_id, array('class'=>'daemon_id'));
}
$attribs = array();

$attribs[] = array('label'=>Yii::t('admin', 'Daemon Operations'), 'value'=>'', 'cssClass'=>'titlerow');
$conStrs = McBridge::get()->conStrings();
if (count($conStrs) > 0)
{
    if (!$daemon_id)
    {
        $keys = array_keys($conStrs);
        $daemon_id = $keys[0];
    }
    $opt = array('all'=>Yii::t('admin', 'All Daemons')) + $conStrs;    

    $attribs[] = array('label'=>'Daemon', 'type'=>'raw',
            'value'=>'<table><tr><td>'.CHtml::dropDownList('master_daemon_id', $daemon_id, $opt).'</td></tr></table>');
}
else
    $attribs[] = array('label'=>Yii::t('admin', 'No daemons found'));
$attribs[] = array('label'=>Yii::t('admin', 'Active Servers'), 'type'=>'raw', 'value'=>
        beginForm($daemon_id)
        .'<table><tr><td>'.addButton(Yii::t('admin', 'Start'), 'active_start').'</td>'
        .'<td>'.addButton(Yii::t('admin', 'Stop'), 'active_stop').'</td></tr>'
        .'<tr><td>'.addButton(Yii::t('admin', 'Restart'), 'active_restart').'</td>'
        .'<td>'.addButton(Yii::t('admin', 'Suspend'), 'active_suspend').'</td></tr></table>',
);
$attribs[] = array('label'=>Yii::t('admin', 'Suspended Servers'), 'type'=>'raw', 'value'=>
        '<table><tr><td>'.addButton(Yii::t('admin', 'Resume'), 'suspended_resume').'</td></tr></table>',
);
$attribs[] = array('label'=>Yii::t('admin', 'Running Servers'), 'type'=>'raw', 'value'=>
        '<table><tr><td>'.addButton(Yii::t('admin', 'Stop'), 'run_stop').'</td></tr>'
        .'<tr><td>'.addButton(Yii::t('admin', 'Restart'), 'run_restart').'</td></tr></table>'
        .CHtml::endForm(),
);
$attribs[] = array('label'=>Yii::t('admin', 'Chat'), 'type'=>'raw', 'value'=>
        beginForm($daemon_id)
        .'<table><tr><td>'.Yii::t('admin', 'Sender').'</td></tr>'
        .'<tr><td>'.CHtml::textField('from', @$_GET['from']).'</td></tr>'
        .'<tr><td>'.Yii::t('admin', 'Message').'</td></tr>'
        .'<tr><td>'.CHtml::textField('message', @$_GET['message'], array('style'=>'width: 294px')).'</td></tr>'
        .'<tr><td>'.addButton(Yii::t('admin', 'Broadcast'), 'run_chat').'</td></td></table>'
        .CHtml::endForm(),
);
$attribs[] = array('label'=>Yii::t('admin', 'Console'), 'type'=>'raw', 'value'=>
        beginForm($daemon_id)
        .'<table><tr><td>'.CHtml::textField('command', @$_GET['command'], array('style'=>'width: 294px'))
        .'<div style="display:none"><input type="text" name="ieBugWorkaround"/></div>'
        .'</td></tr>'
        .'<tr><td>'.addButton(Yii::t('admin', 'Execute'), 'run_console').'</td></tr></table>'
        .CHtml::endForm(),
);

$this->widget('zii.widgets.CDetailView', array(
    'data'=>array(),
    'attributes'=>$attribs,
));

?>
<br/>

<?php
$attribs = array();

$attribs[] = array('label'=>Yii::t('admin', 'Global Operations'), 'value'=>'', 'cssClass'=>'titlerow');
$attribs[] = array('label'=>Yii::t('admin', 'Cleanup Players Table'), 'type'=>'raw', 'value'=>
        beginForm($daemon_id)
        .addButton(Yii::t('admin', 'Cleanup'), 'global_clean_players', $playersCleanupMsg),
        'hint'=>Yii::t('admin', 'Deletes all players that have the same role as the Default Role of their server'),
    );
$attribs[] = array('label'=>Yii::t('admin', 'Clear Command Cache'), 'type'=>'raw', 'value'=>
        addButton(Yii::t('admin', 'Clear'), 'global_clear_cmdcache', $cmdcacheCleanupMsg)
        .CHtml::endForm(),
    );

$this->widget('zii.widgets.CDetailView', array(
    'data'=>array(),
    'attributes'=>$attribs,
));

echo CHtml::script('
    $(document).ready(function() {
        $("#master_daemon_id").change(function() {
            $(".daemon_id").val($("#master_daemon_id option:selected").val());
        });
    });');

?>
<br/>

<?php if(Yii::app()->user->hasFlash('operations')): ?>
<div class="flash-success">
    <?php echo Yii::app()->user->getFlash('operations'); ?>
</div>
<?php endif ?>


