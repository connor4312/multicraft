<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->breadcrumbs=array(
    Yii::t('mc', 'Users')=>array('index'),
    $model->isNewRecord ? Yii::t('mc', 'New User') : CHtml::encode($model->name),
);

Yii::app()->getClientScript()->registerCoreScript('jquery');

if (Yii::app()->user->isSuperuser() && !$model->isNewRecord && $model->name != Yii::app()->user->superuser)
{
$this->menu=array(
    array(
        'label'=>Yii::t('mc', 'Delete User'),
        'url'=>'#',
        'linkOptions'=>array(
            'submit'=>array('delete','id'=>$model->id),
            'confirm'=>Yii::t('mc', 'Are you sure you want to delete this user?'),
            'csrf'=>true,
        ),
        'icon'=>'user_del',
    ),
);
}
if (Yii::app()->user->isSuperuser()  && !$model->isNewRecord)
{
    if (!$model->api_key)
    {
        $this->menu[] = array(
                'label'=>Yii::t('mc', 'Generate API Key'),
                'url'=>'#',
                'linkOptions'=>array(
                    'submit'=>array('view','id'=>$model->id),
                    'confirm'=>Yii::t('mc', 'Allow this user to access the Multicraft API?'),
                    'params'=>array('action'=>'new_api_key'),
                    'csrf'=>true,
                ),
                'icon'=>'key_new',
            );
    }
    else
    {
        $this->menu[] = array(
                'label'=>Yii::t('mc', 'Remove API Key'),
                'url'=>'#',
                'linkOptions'=>array(
                    'submit'=>array('view','id'=>$model->id),
                    'confirm'=>Yii::t('mc', 'Disable Multicraft API access for this user?'),
                    'params'=>array('action'=>'del_api_key'),
                    'csrf'=>true,
                ),
                'icon'=>'key_del',
            );
    }
}
if (Yii::app()->user->isSuperuser() || Yii::app()->params['hide_userlist'] !== true)
{
    $this->menu[] = array(
        'label'=>Yii::t('mc', 'Back'),
        'url'=>array('user/index'),
        'icon'=>'back'
    );
}
?>

<?php
if (!$edit)
{
    $attribs = array('name');
}
else
{
    $form=$this->beginWidget('CActiveForm', array(
            'id'=>'user-form',
            'enableAjaxValidation'=>false,
        ));

    if (Yii::app()->user->isSuperuser())
    {
        if ($model->name != Yii::app()->user->superuser)
        {
            $attribs[] = array('label'=>$form->labelEx($model,'name'), 'type'=>'raw',
                'value'=>$form->textField($model,'name').' '.$form->error($model,'name'));
        }
        if (Yii::app()->user->name == $model->name || $model->name != Yii::app()->user->superuser)
        {
            if ($model->name != Yii::app()->user->superuser)
            {
                $attribs[] = array('label'=>$form->labelEx($model,'global_role'), 'type'=>'raw',
                    'value'=>$form->dropDownList($model,'global_role',$allRoles)
                        .' '.$form->error($model,'global_role'),
                    'hint'=>Yii::t('mc', 'Role that applies for all servers'));
            }
            if ($model->api_key)
            {
                $value = CHtml::textField('ak', $model->api_key, array('id'=>'apiKeyBox', 'readonly'=>'readonly'));
                $attribs[] = array('label'=>$form->labelEx($model,'api_key'), 'type'=>'raw',
                    'value'=>$value.' '.$form->error($model,'api_key'));
            }
        }
    }
    else if ($model->api_key)
        $attribs[] = array('label'=>$form->labelEx($model,'api_key'), 'type'=>'raw',
                'value'=>CHtml::textField('ak', $model->api_key, array('id'=>'apiKeyBox', 'readonly'=>'readonly')));
    $attribs[] = array('label'=>$form->labelEx($model,'lang'), 'type'=>'raw',
            'value'=>$form->dropDownList($model,'lang', $this->languageSelection()).' '.$form->error($model,'lang'));
    $attribs[] = array('label'=>$form->labelEx($model,'email'), 'type'=>'raw',
            'value'=>$form->textField($model,'email').' '.$form->error($model,'email'));
    $attribs[] = array('label'=>$form->labelEx($model,'password'), 'type'=>'raw',
            'value'=>$form->passwordField($model,'password').' '.$form->error($model,'password'));
    if (Yii::app()->user->isSuperuser() && Yii::app()->params['mail_welcome'])
    {
        $attribs[] = array('label'=>$model->isNewRecord ? Yii::t('mc', 'Send Welcome Mail')
                : Yii::t('mc', 'Send new password if changed'), 'type'=>'raw',
            'value'=>CHtml::checkBox('send_data', true));
    }
    $attribs[] = array('label'=>'', 'type'=>'raw', 'value'=>CHtml::submitButton($model->isNewRecord ? Yii::t('mc', 'Create') : Yii::t('mc', 'Save')));
}


$this->widget('zii.widgets.CDetailView', array(
    'data'=>$model,
    'attributes'=>$attribs,
)); 

if ($edit)
    $this->endWidget();

echo CHtml::script('$("#apiKeyBox").focus(function() { this.select(); });');            
?>

<?php if(Yii::app()->user->hasFlash('user')): ?>
<div class="flash-success">
    <?php echo Yii::app()->user->getFlash('user'); ?>
</div>
<?php endif ?>
<br/>
<br/>
<?php
if (!$model->isNewRecord)
{
    $this->widget('zii.widgets.CListView', array(
        'emptyText'=>'',
        'dataProvider'=>$servers,
        'itemView'=>'_server',
        'ajaxUpdate'=>false,
        'sortableAttributes'=>array(
            'name'=>Yii::t('mc', 'Name'),
        ),
    ));
}

