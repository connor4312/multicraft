<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle = Yii::app()->name . ' - '.Yii::t('mc', 'Create Config File Setting');

$this->breadcrumbs=array(
    Yii::t('mc', 'Settings')=>array('daemon/index'),
    Yii::t('mc', 'Config File Settings')=>array('index'),
    $model->isNewRecord ? Yii::t('mc', 'New Config File Setting') : CHtml::encode($model->name),
);

if (!$model->isNewRecord)
{
    $this->menu=array(
        array(
            'label'=>Yii::t('mc', 'Delete'),
            'url'=>'#',
            'linkOptions'=>array(
                'submit'=>array('delete','id'=>$model->id),
                'confirm'=>Yii::t('mc', 'Are you sure you want to delete this item?'),
                'csrf'=>true,
            ),
            'icon'=>'config',
        ),
    );
}
$this->menu[] = array(
    'label'=>Yii::t('mc', 'Back'),
    'url'=>array('index'),
    'icon'=>'back'
);

$form=$this->beginWidget('CActiveForm', array(
    'id'=>'configFile-form',
    'enableAjaxValidation'=>false,
));


$attribs = array();
$attribs[] = array('label'=>$form->labelEx($model,'name'), 'type'=>'raw',
        'value'=>$form->textField($model,'name').' '.$form->error($model,'name'));
$attribs[] = array('label'=>$form->labelEx($model,'enabled'), 'type'=>'raw',
        'value'=>$form->dropDownList($model,'enabled',array('1'=>Yii::t('mc', 'True'), '0'=>Yii::t('mc', 'False')))
        .' '.$form->error($model,'enabled'));
$attribs[] = array('label'=>$form->labelEx($model,'description'), 'type'=>'raw',
        'value'=>$form->textField($model,'description').' '.$form->error($model,'description'));
$attribs[] = array('label'=>$form->labelEx($model,'file'), 'type'=>'raw',
        'value'=>$form->textField($model,'file').' '.$form->error($model,'file'));
$attribs[] = array('label'=>$form->labelEx($model,'dir'), 'type'=>'raw',
        'value'=>$form->textField($model,'dir').' '.$form->error($model,'dir'));
$attribs[] = array('label'=>$form->labelEx($model,'options'), 'type'=>'raw',
        'value'=>$form->textArea($model,'options',array('rows'=>10, 'cols'=>60)).' '.$form->error($model,'options'));
$attribs[] = array('label'=>$form->labelEx($model,'type'), 'type'=>'raw',
        'value'=>$form->dropDownList($model,'type',array(''=>Yii::t('mc', 'List'),
            'properties'=>Yii::t('mc', 'Property File')))
        .' '.$form->error($model,'type'));
$attribs[] = array('label'=>'', 'type'=>'raw', 'value'=>CHtml::submitButton(
            $model->isNewRecord ? Yii::t('mc', 'Create') : Yii::t('mc', 'Save')));

$this->widget('zii.widgets.CDetailView', array(
    'data'=>$model,
    'attributes'=>$attribs,
)); 
$this->endWidget();

?>
<?php if(Yii::app()->user->hasFlash('configFile')): ?>
<div class="flash-success">
    <?php echo Yii::app()->user->getFlash('configFile'); ?>
</div>
<?php endif ?>
