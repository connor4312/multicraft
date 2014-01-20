<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->breadcrumbs=array(
    Yii::t('mc', 'Servers')=>array('server/index'),
    $sv ? @Server::model()->findByPk($sv)->name : Yii::t('mc', 'All')
        => $sv ? array('server/view', 'id'=>$sv) : array('/server'),
    Yii::t('mc', 'Commands')=>$sv ? array('command/index', 'sv'=>$sv) : array('command/admin'),
    $model->isNewRecord ? Yii::t('mc', 'New Command') : CHtml::encode($model->name),
);

if (!$model->isNewRecord)
{
$this->menu=array(
    array(
        'label'=>Yii::t('mc', 'Delete Command'),
        'url'=>'#',
        'linkOptions'=>array(
            'submit'=>array('delete', 'id'=>$model->id),
            'confirm'=>Yii::t('mc', 'Are you sure you want to delete this item?'),
            'csrf'=>true,
        ),
        'visible'=>$edit,
        'icon'=>'command_del'
    ),
);
}
$this->menu[] = array(
    'label'=>Yii::t('mc', 'Back'),
    'url'=>($sv ? array('command/index', 'sv'=>$sv) : array('command/admin')),
    'icon'=>'back'
);
?>

<?php
if (!$edit)
{
    $attribs = array('name', 'level', 'prereq', 'chat', 'response', 'run');
}
else
{
    $form=$this->beginWidget('CActiveForm', array(
            'id'=>'command-form',
            'enableAjaxValidation'=>false,
        ));

    $userRoles = array_combine(User::$roleLevels, User::getRoleLabels());
    $userRoles[0] = Yii::t('mc', 'None');

    if (Yii::app()->user->isSuperuser())
    {
        $attribs[] = array('label'=>$form->labelEx($model,'server_id'), 'type'=>'raw',
            'value'=>$form->textField($model,'server_id').' '.$form->error($model,'server_id'),
            'hint'=>Yii::t('mc', 'The server ID, use 0 for "Global"'));
        $attribs[] = array('label'=>$form->labelEx($model,'hidden'), 'type'=>'raw',
                'value'=>$form->checkBox($model,'hidden')
                    .' '.$form->error($model,'hidden'),
                'hint'=>Yii::t('mc', 'Only show this command to other superusers'));
    }
    $attribs[] = array('label'=>$form->labelEx($model,'name'), 'type'=>'raw',
        'value'=>$form->textField($model,'name').' '.$form->error($model,'name'));
    $attribs[] = array('label'=>$form->labelEx($model,'level'), 'type'=>'raw',
        'value'=>$form->dropDownList($model,'level',$userRoles)
            .' '.$form->error($model,'level'));
    $attribs[] = array('label'=>$form->labelEx($model,'prereq'), 'type'=>'raw',
        'value'=>$form->dropDownList($model,'prereq',
            array('0'=>'None') + CHtml::listData(Command::model()->findAllByAttributes(
            array('server_id'=>$sv)), 'id', 'name'))
            .' '.$form->error($model,'prereq'),
        'hint'=>Yii::t('mc', 'This command has to be run before'));
    $attribs[] = array('label'=>$form->labelEx($model,'chat'), 'type'=>'raw',
        'value'=>$form->textField($model,'chat')
            .' '.$form->error($model,'chat'),
        'hint'=>Yii::t('mc', 'The users message has to begin with this'));
    $attribs[] = array('label'=>$form->labelEx($model,'response'), 'type'=>'raw',
        'value'=>$form->textField($model,'response')
            .' '.$form->error($model,'response'),
        'hint'=>Yii::t('mc', 'Whispered to the player'));
    $attribs[] = array('label'=>$form->labelEx($model,'run'), 'type'=>'raw',
        'value'=>$form->textField($model,'run')
            .' '.$form->error($model,'run'),
        'hint'=>Yii::t('mc', 'Run this Minecraft command (<a href="http://www.multicraft.org/site/page?view=usage#commands" target="_blank">see usage</a> for arguments and multiple commands)'));
    $attribs[] = array('label'=>'', 'type'=>'raw', 'value'=>CHtml::submitButton($model->isNewRecord ? Yii::t('mc', 'Create') : Yii::t('mc', 'Save')));
}

$this->widget('zii.widgets.CDetailView', array(
    'data'=>$model,
    'attributes'=>$attribs,
)); 

if ($edit)
    $this->endWidget();
?>
<?php if(Yii::app()->user->hasFlash('command')): ?>
<div class="flash-success">
    <?php echo Yii::app()->user->getFlash('command'); ?>
</div>
<?php endif ?>

