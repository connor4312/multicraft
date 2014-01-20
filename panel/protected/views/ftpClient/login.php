<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle=Yii::app()->name . ' - '.Yii::t('mc', 'Minecraft FTP Client').' '.Yii::t('mc', 'Login');
$this->breadcrumbs=array(
    Yii::t('mc', 'FTP Client')=>array('index', 'id'=>$id),
    Yii::t('mc', 'FTP Server Login'),
);

Yii::app()->getClientScript()->registerCoreScript('jquery');

if ($id)
{
    $this->menu = array(
        array(
            'label'=>Yii::t('mc', 'Back'),
            'url'=>array('server/view', 'id'=>$id),
            'icon'=>'back',
        )
    );
}
?>

<?php echo Yii::t('mc', 'Multicraft allows you to access your files via FTP.') ?><br/>
<?php echo Yii::t('mc', 'You can either use the built-in FTP client or any other FTP client installed on your system.') ?><br/>
<br/>

<?php

$attribs = array();
$attribs[] = array('label'=>CHtml::label(Yii::t('mc', 'Server to connect to'), 'server_id'), 'type'=>'raw',
    'value'=>CHtml::dropDownList('server_id', $id, $serverList));
$attribs[] = array('label'=>CHtml::label(Yii::t('mc', 'Host'), false), 'type'=>'raw',
    'value'=>'<div id="ftp_ip">'.CHtml::encode($id ? @$daemons[$id]['ip'] : '-').'</div>');
$attribs[] = array('label'=>CHtml::label(Yii::t('mc', 'Port'), false), 'type'=>'raw',
    'value'=>'<div id="ftp_port">'.CHtml::encode($id ? @$daemons[$id]['port'] : '-').'</div>');
$attribs[] = array('label'=>CHtml::label(Yii::t('mc', 'FTP Username'), false), 'type'=>'raw',
    'value'=>'<div id="username">'.CHtml::encode($id ? Yii::app()->user->name.'.'.$id : $sel).'</div>');
$attribs[] = array('label'=>CHtml::label(Yii::t('mc', 'Multicraft Password'), 'password'), 'type'=>'raw',
    'value'=>CHtml::passwordField('password').' <span class="hint">'.($havePw ? Yii::t('mc', '(Leave empty to use cached Password)') : '').'</span>');
$attribs[] = array('label'=>'', 'type'=>'raw',
    'value'=>CHtml::submitButton(Yii::t('mc', 'Login'), array('id'=>'login_button')));
?>

<?php echo CHtml::beginForm() ?>

<?php
$this->widget('zii.widgets.CDetailView', array(
    'data'=>array(),
    'attributes'=>$attribs,
)); 
?>

<?php echo CHtml::endForm(); ?>

<?php
echo CHtml::script('
    $("#server_id").change(function() {
        var daemons = '.CJSON::encode($daemons).';
        id = parseInt($("#server_id").children("option:selected").val());
        if (id)
        {
            $("#ftp_ip").html(daemons[id].ip);
            $("#ftp_port").html(daemons[id].port);
            $("#username").html("'.CHtml::encode(Yii::app()->user->name).'." + id);
            $("#login_button").removeAttr("disabled");
            $("#password").removeAttr("disabled");
        }
        else
        {
            $("#ftp_ip").html("'.addslashes($sel).'");
            $("#ftp_port").html("-");
            $("#username").html("-");
            $("#login_button").attr("disabled", true);
            $("#password").attr("disabled", true);
        }
    });
    $(document).ready(function() { $("#server_id").change(); });
');
?>
