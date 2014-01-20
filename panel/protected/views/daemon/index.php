<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle=Yii::app()->name . ' - '.Yii::t('admin', 'Minecraft Manager Settings');
$this->breadcrumbs=array(
    Yii::t('admin', 'Settings')=>array('index'),
    Yii::t('admin', 'Settings'),
);

$this->menu=array(
        array(
            'label'=>Yii::t('admin', 'Update Minecraft'),
            'url'=>array('daemon/updateMC'),
            'icon'=>'update',
        ),
        array(
            'label'=>Yii::t('admin', 'Multicraft Status'),
            'url'=>array('daemon/status'),
            'icon'=>'status',
        ),
        array(
            'label'=>Yii::t('admin', 'Panel Configuration'),
            'url'=>array('daemon/panelConfig'),
            'icon'=>'config',
        ),
        array(
            'label'=>Yii::t('admin', 'Statistics'),
            'url'=>array('daemon/statistics'),
            'icon'=>'statistics',
        ),
        array(
            'label'=>Yii::t('admin', 'Operations'),
            'url'=>array('daemon/operations'),
            'icon'=>'operations',
        ),
        array(
            'label'=>Yii::t('admin', 'Config File Settings'),
            'url'=>array('configFile/index'),
            'icon'=>'file',
        ),
    );

Yii::app()->clientScript->registerCssFile(Theme::css('detailview.css'));
Yii::app()->getClientScript()->registerCoreScript('jquery');
?>

<?php echo CHtml::beginForm() ?>
<?php echo CHtml::hiddenField('submit', 'true') ?>
<table class="detail-view">
<tr class="titlerow">
    <td><?php echo Yii::t('admin', 'Setting') ?></td>
    <td></td>
    <td><?php echo Yii::t('admin', 'Default') ?></td>
</tr>
<?php
echo CHtml::css('.adv { display: none; }');
$i = 0;
$adv = false;
foreach ($settings as $name=>$setting): ?>
<?php if (@$setting['adv'] && !$adv): ?>
<tr class="<?php echo ($i++ % 2) ? 'even' : 'odd' ?>"style="height: 32px" >
    <td><?php echo Theme::img('icons/closed.png', '', array('id'=>'advImg', 'onclick'=>'return checkAdv()')) ?></td>
    <td><?php echo CHtml::link(Yii::t('admin', 'Show Advanced Options'), '#', array('id'=>'advTxt', 'onclick'=>'return checkAdv()')) ?></td>
    <td></td>
</tr>
<?php $adv = true ?>
<?php endif ?>
<tr class="<?php echo ($i++ % 2) ? 'even' : 'odd' ?><?php echo (@$setting['adv'] ? ' adv' : '') ?>">
    <td><?php echo CHtml::label($setting['label'], 'Setting['.$name.']') ?></td>
    <?php if ($setting['unit'] == 'bool'): ?>
    <td><?php echo CHtml::checkBox('Setting['.$name.']', $setting['value']) ?></td>
    <td><?php echo $setting['default'] ? Yii::t('admin', 'true') : Yii::t('admin', 'false') ?></td>
    <?php elseif (is_array($setting['unit'])): ?>
    <td><?php echo CHtml::dropDownList('Setting['.$name.']', $setting['value'], $setting['unit']) ?></td>
    <td><?php echo $setting['unit'][$setting['default']] ?></td>
    <?php else: ?>
    <td><?php echo CHtml::textField('Setting['.$name.']', $setting['value']).' '.$setting['unit'] ?></td>
    <td><?php echo $setting['default'].' '.$setting['unit'] ?></td>
    <?php endif ?>
</tr>
<?php endforeach ?>
<tr>
    <td></td>
    <td><?php echo CHtml::submitButton(Yii::t('admin', 'Save')) ?></td>
    <td></td>
</tr>
</table>
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
    '.(@$advanced ? '$(function() { checkAdv(); });' : '').'
');
echo CHtml::endForm();
?>
<br/>
