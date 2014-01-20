<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle = Yii::app()->name . ' - '.CHtml::encode($plugin->plugin_name);

$this->breadcrumbs=array(
    Yii::t('mc', 'Servers')=>array('index'),
    $model->name=>array('view', 'id'=>$model->id),
    Yii::t('mc', 'Plugins')=>array('bgPlugins', 'id'=>$model->id),
    CHtml::encode($plugin->plugin_name),
);

$this->menu=array(
    array(
        'label'=>Yii::t('mc', 'Back'),
        'url'=>array('server/bgPlugins', 'id'=>$model->id, 'installed'=>$installed),
        'icon'=>'back',
    ),
);
?>

<?php if(Yii::app()->user->hasFlash('server')): ?>
<div class="flash-error">
    <?php echo Yii::app()->user->getFlash('server'); ?>
</div>
<?php endif ?>

<h1><?php echo CHtml::encode($plugin->plugin_name) ?></h1>
<?php echo Yii::t('mc', 'Version') ?> <?php echo CHtml::encode($plugin->version) ?>
 <?php echo Yii::t('mc', 'by {author}', array('{author}'=>CHtml::encode($plugin->authors))) ?><br/>
<br/>
<h5><?php echo Yii::t('mc', 'Description') ?></h5>
<?php echo CHtml::encode($plugin->desc) ?><?php echo CHtml::link(Yii::t('mc', 'more'), $plugin->link) ?><br/>
<br/>
<h5><?php echo Yii::t('mc', 'Plugin Page') ?></h5>
<?php echo CHtml::link(CHtml::encode($plugin->link), $plugin->link) ?><br/>
<br/>
<h5><?php echo Yii::t('mc', 'Status') ?></h5>
<?php
function form($name, $label, $action, $dis = array())
{
    echo CHtml::beginForm('', 'POST');
    echo CHtml::submitButton($label, $dis + array('style'=>'float: left'));
    echo CHtml::hiddenField('action', $action);
    echo CHtml::hiddenField('name', $name);
    echo CHtml::endForm();
}

$dis = array('disabled'=>'disabled');
$d = array();
$i = $info ? $info->installed : '';
if ($info && $info->disabled)
    $i = 'disabled';
$show = $i;

if ($action == 'install' || $action == 'update')
    $show = 'installed';
else if ($action == 'disable')
    $show = 'disabled';
else if ($action == 'enable')
    $show = 'installed';
else if ($action == 'remove')
    $show = '';

echo '<div id="regular"'.(($i != $show) ? ' style="display: none"' : '').'>';
if ($show == 'installed')
{
    echo Yii::t('mc', 'Installed and up to date');
    if ($i == $show)
        echo ' ('.Yii::t('mc', 'Version').': '.CHtml::encode(@$info->version).')';
    echo '<br/><br/>';
    form($plugin->name, Yii::t('mc', 'Disable'), 'disable', $d);
    form($plugin->name, Yii::t('mc', 'Remove'), 'remove', $d);
}
else if ($show == 'outdated')
{
    echo Yii::t('mc', 'Outdated').' ('.Yii::t('mc', 'Installed Version').': ';
    echo CHtml::encode(@$info->version).')';
    echo '<br/><br/>';
    form($plugin->name, Yii::t('mc', 'Update'), 'update', $d);
    form($plugin->name, Yii::t('mc', 'Remove'), 'remove', $d);
}
else if ($show == 'disabled')
{
    echo Yii::t('mc', 'Disabled').' ('.Yii::t('mc', 'Installed Version').': ';
    echo CHtml::encode(@$info->version).')';
    echo '<br/><br/>';
    form($plugin->name, Yii::t('mc', 'Enable'), 'enable', $d);
    form($plugin->name, Yii::t('mc', 'Remove'), 'remove', $d);
}
else
{
    echo Yii::t('mc', 'Not installed');
    echo '<br/><br/>';
    form($plugin->name, Yii::t('mc', 'Install'), 'install', $d);
}
echo '</div>';
echo '<div id="pending"'.(($i == $show) ? ' style="display: none"' : '').'>';
?>
<div class="pluginActionPending"><?php echo Theme::img('gridview/loading.gif') ?></div>
&nbsp;&nbsp;
<?php echo Yii::t('mc', 'Please wait for the action to complete.') ?>
</div>
<?php

if ($i != $show)
echo CHtml::script('
    function refresh()
    {
        '.CHtml::ajax(array('type'=>'POST', 'dataType'=>'json',
                'success'=>'js:set_status', 'data'=>array('ajax'=>'get_status',
                    Yii::app()->request->csrfTokenName=>Yii::app()->request->csrfToken,
                )
            )).'
    }
    function set_status(data)
    {
        var expect = "'.$show.'";
        if (expect == data)
        {
            $("#pending").hide();
            $("#regular").show();
            return;
        }
        scheduleRefresh();
    }
    function scheduleRefresh(delay)
    {
        setTimeout(function() { refresh(); }, '.$this->ajaxUpdateInterval.');
    }
    $(document).ready(function() {
        scheduleRefresh();
    });
');
?>
<br/>
<br/>
<br/>
