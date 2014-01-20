<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

$this->pageTitle=Yii::app()->name . ' - '.Yii::t('admin', 'Multicraft Statistics');
$this->breadcrumbs=array(
    Yii::t('admin', 'Settings')=>array('index'),
    Yii::t('admin', 'Multicraft Statistics'),
);

$this->menu=array(
    array(
        'label'=>Yii::t('admin', 'Back'),
        'url'=>array('daemon/index'),
        'icon'=>'back',
    ),
);

Yii::app()->clientScript->registerCssFile(Theme::css('detailview.css'));
Yii::app()->getClientScript()->registerCoreScript('jquery');

function trclass($reset = false)
{
    static $nr = 0;
    if ($reset)
        $nr = 0;
    if ($nr++ % 2)
        echo 'even';
    else
        echo 'odd';
}

?>

<table class="detail-view">
<tr class="titlerow">
    <td><?php echo Yii::t('admin', 'Statistics') ?></td>
    <td width="200" height="30"></td>
</tr>
<tr class="<?php trclass() ?>">
    <td><?php echo Yii::t('admin', 'Total Servers') ?></td>
    <td><?php echo $servers ?></td>
</tr>
<tr class="<?php trclass() ?>">
    <td><?php echo Yii::t('admin', 'Active Servers') ?></td>
    <td><?php echo $activeServers ?></td>
</tr>
<tr class="<?php trclass() ?>">
    <td><?php echo Yii::t('admin', 'Suspended Servers') ?></td>
    <td><?php echo ($servers - $activeServers) ?></td>
</tr>
<tr class="<?php trclass() ?>">
    <td><?php echo Yii::t('admin', 'Daemons') ?></td>
    <td><?php echo $daemons ?></td>
</tr>
<tr class="<?php trclass() ?>">
    <td><?php echo Yii::t('admin', 'Average servers per daemon') ?></td>
    <td><?php echo number_format($activeSvPerDaemon, 2).' <span style="float: right">('.number_format($svPerDaemon, 2).')</span>' ?></td>
</tr>
<tr class="<?php trclass() ?>">
    <td><?php echo Yii::t('admin', 'Total player slots') ?></td>
    <td><?php echo $activeSlots.' <span style="float: right">('.$slots.')</span>' ?></td>
</tr>
<tr class="<?php trclass() ?>">
    <td><?php echo Yii::t('admin', 'Total memory assigned') ?></td>
    <td><?php echo $activeMemory.' <span style="float: right">('.$memory.')</span>' ?></td>
</tr>
</table>
<span style="float:right; font-size: 10px"><?php echo Yii::t('admin', 'Values in brackets include suspended servers.') ?></span>
<br/>
<table class="detail-view">
<tr class="titlerow">
    <td><?php echo Yii::t('admin', 'Live Statistics') ?></td>
    <td width="200" height="30" id="status"><?php echo Theme::img('changing.png') ?> pending</td>
</tr>
<tr class="<?php trclass(true) ?>">
    <td><?php echo Yii::t('admin', 'Online servers') ?></td>
    <td id="servers"></td>
</tr>
<tr class="<?php trclass() ?>">
    <td><?php echo Yii::t('admin', 'Online players') ?></td>
    <td id="players"></td>
</tr>
<tr class="<?php trclass() ?>">
    <td><?php echo Yii::t('admin', 'Average players per server') ?></td>
    <td id="avg_players"></td>
</tr>
<tr class="<?php trclass() ?>">
    <td><?php echo Yii::t('admin', 'Total memory assigned to online servers') ?></td>
    <td id="memory"></td>
</tr>
</table>

<?php

echo CHtml::script('
function stats_response(data)
{
    for (var key in data)
        $("#" + key).html(data[key]);
    $("#status").html("");
}

function query_stats()
{
'.CHtml::ajax(array(
    'type'=>'POST',
    'dataType'=>'json',
    'data'=>array(
        'ajax'=>'stats',
        Yii::app()->request->csrfTokenName=>Yii::app()->request->csrfToken,
        ),
    'success'=>'stats_response'
    )).'
}

query_stats();


');
    

