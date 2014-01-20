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
    CHtml::encode($name),
);

$this->menu = array(
    array(
        'label'=>Yii::t('mc', 'Back'),
        'url'=>array('server/configs', 'id'=>$model->id),
        'icon'=>'back',
    )
);

if ($error): ?>
<div class="flash-error">
    <?php echo CHtml::encode($error) ?>
</div>

<?php
endif;

$form=$this->beginWidget('CActiveForm', array(
    'id'=>'cfgfile-form',
    'enableAjaxValidation'=>false,
));

if ($type == 'properties')
{
    foreach ($options as $id=>$info)
    {
        if ($ro)
            $value = CHtml::encode($info['value']);
        else if (@is_array($info['select']))
            $value = CHtml::dropDownList('option['.$id.']', $info['value'], $info['select']);
        else
            $value = CHtml::textField('option['.$id.']', $info['value']);
        $attribs[] = array('label'=>@CHtml::encode($info['name']), 'type'=>'raw', 'value'=>$value);
    }
}
else
{
    $htmlOpt = array('style'=>'width: 580px; height: 480px', 'wrap'=>'off');
    if ($ro)
        $htmlOpt['readonly'] = 'readonly';
    $attribs[] = array('label'=>CHtml::encode($name), 'type'=>'raw', 'value'=>
        CHtml::textArea('list', $list, $htmlOpt));
}

if (!$ro)
    $attribs[] = array('label'=>'', 'type'=>'raw', 'value'=>CHtml::submitButton(Yii::t('mc', 'Save')));

$this->widget('zii.widgets.CDetailView', array(
    'data'=>array(),
    'attributes'=>$attribs,
));

echo CHtml::hiddenField('save', 'true');
$this->endWidget();
?>


