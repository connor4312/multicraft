<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle = Yii::app()->name . ' - '.Yii::t('mc', 'Edit Config File');

$this->breadcrumbs=array(
    Yii::t('mc', 'Servers')=>array('index'),
    $model->name=>array('view', 'id'=>$model->id),
    Yii::t('mc', 'Config Files')=>array('configs', 'id'=>$model->id),
    Yii::t('mc', 'Permissions Plugin'),
);

$this->menu = array(
    array(
        'label'=>Yii::t('mc', 'Back'),
        'url'=>array('server/configs', 'id'=>$model->id),
        'icon'=>'back',
    )
);

echo CHtml::css('table.detail-view .stdtable td { border: none }' );

if ($error): ?>
<div class="flash-error">
    <?php echo $error ?>
</div>

<?php
endif;

$form=$this->beginWidget('CActiveForm', array(
    'id'=>'cfgfile-form',
    'enableAjaxValidation'=>false,
));

function groupForm($role)
{
    ob_start();
?>
<table class="stdtable">
<tr>
    <td>
        <?php echo CHtml::label(Yii::t('mc', 'Prefix'), 'prefix_'.$role) ?>
    </td>
    <td>
        <?php echo CHtml::label(Yii::t('mc', 'Suffix'), 'suffix_'.$role) ?>
    </td>
    <td>
        <?php echo CHtml::label(Yii::t('mc', 'Can Build'), 'build_'.$role) ?>
    </td>
</tr>
<tr>
    <td>
        <?php echo CHtml::textField('prefix_'.$role, @$_POST['prefix_'.$role]) ?>
    </td>
    <td>
        <?php echo CHtml::textField('suffix_'.$role, @$_POST['suffix_'.$role]) ?>
    </td>
    <td>
        <?php echo CHtml::dropDownList('build_'.$role, @$_POST['build_'.$role], array(0=>Yii::t('mc', 'Yes'), 1=>Yii::t('mc', 'No'))) ?>
    </td>
</tr>
<tr>
    <td colspan="3">
        <?php echo CHtml::label(Yii::t('mc', 'Permissions, comma sepparated'), 'perms_'.$role) ?>
    </td>
</tr>
<tr>
    <td colspan="3">
        <?php echo CHtml::textField('perms_'.$role, @$_POST['perms_'.$role], array('style'=>'width: 540px')) ?>
    </td>
</tr>
</table>
<?php
    return ob_get_clean();
}

foreach (User::$roles as $role)
{
    if ($role != 'none')
        $attribs[] = array('label'=>User::getRoleLabel($role), 'type'=>'raw', 'value'=>groupForm($role));
}

$attribs[] = array('label'=>'', 'type'=>'raw', 'value'=>
    Yii::t('mc', 'If you change the role of a player you need to save this form again and run "reload" in the console').'<br/><br/>'
    .Yii::t('mc', 'Warning: This will overwrite your existing users.yml and groups.yml!').'<br/><br/>'
    .CHtml::submitButton(Yii::t('mc', 'Save')));

$this->widget('zii.widgets.CDetailView', array(
    'data'=>array(),
    'attributes'=>$attribs,
));

echo CHtml::hiddenField('save', 'true');
$this->endWidget();
?>


