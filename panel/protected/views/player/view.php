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
    $sv ? @Server::model()->findByPk((int)$sv)->name : Yii::t('mc', 'All')=>$sv ? array('server/view', 'id'=>$sv) : array('/server'),
    Yii::t('mc', 'Players')=>$sv ? array('player/index', 'sv'=>$sv) : array('player/admin'),
    $model->isNewRecord ? Yii::t('mc', 'New Player') : CHtml::encode($model->name),
);

if (!$model->isNewRecord)
{
$this->menu=array(
    array(
        'label'=>Yii::t('mc', 'Delete Player'),
        'url'=>'#',
        'linkOptions'=>array(
            'submit'=>array('delete', 'id'=>$model->id),
            'confirm'=>Yii::t('mc', 'Are you sure you want to delete this player?'),
            'csrf'=>true,
        ),
        'visible'=>$edit,
        'icon'=>'player_del',
    ),
);
}

$this->menu[] = array(
    'label'=>Yii::t('mc', 'Back'),
    'url'=>($sv ? array('player/index', 'sv'=>$sv) : array('player/admin')),
    'icon'=>'back',
);

?>

<?php
if (!$edit)
{
    $attribs = array('name', 'level'=>array('name'=>'level',
        'value'=>User::getRoleLabel(User::getLevelRole($model->level))));
    $user = User::model()->findByPk((int)$model->user);
    if ($user)
        $attribs[] = array('label'=>Yii::t('mc', 'Belongs to'), 'type'=>'raw',
            'value'=>CHtml::encode($user->name));
}
else
{
    $form=$this->beginWidget('CActiveForm', array(
            'id'=>'player-form',
            'enableAjaxValidation'=>false,
        ));

    if (!$sv)
        $attribs[] = array('label'=>$form->labelEx($model,'server_id'), 'type'=>'raw',
                'value'=>$form->dropDownList($model,'server_id', CHtml::listData(Server::model()->findAll(),
                    'id', 'name')).' '.$form->error($model,'server_id'));
    $attribs[] = array('label'=>$form->labelEx($model,'name'), 'type'=>'raw',
            'value'=>$form->textField($model,'name').' '.$form->error($model,'name'));  
    $attribs[] = array('label'=>$form->labelEx($model,'level'), 'type'=>'raw',
            'value'=>$form->dropDownList($model,'level',$playerRoles)
            .' '.$form->error($model,'level'));
    if (count(@$users))
    {
        $attribs[] = array('label'=>Yii::t('mc', 'Assign to user'), 'type'=>'raw',
            'value'=>CHtml::dropDownList('user-assign', $model->user,
                array('0'=>Yii::t('mc', 'None')) + CHtml::listData($users, 'id', 'name')
            ));
    }
    $attribs[] = array('label'=>$form->labelEx($model,'banned'), 'type'=>'raw',
            'value'=>$form->dropDownList($model, 'banned', array(''=>Yii::t('mc', 'False'), 'true'=>Yii::t('mc', 'True'))));
    $attribs[] = array('label'=>'', 'type'=>'raw', 'value'=>CHtml::submitButton($model->isNewRecord ? Yii::t('mc', 'Create') : Yii::t('mc', 'Save')));
}
if (!$model->isNewRecord)
{
    $attribs[] = 'status';
    $attribs[] = array('name'=>'lastseen', 'value'=>$model->lastseen ? @date(Yii::t('mc', 'd. M Y, H:i'), (int)$model->lastseen) : Yii::t('mc', 'Never'));
    if ($viewDetails)
        $attribs = array_merge($attribs, array('ip', 'previps', 'quitreason'));
}
if (@$give && $model->status == 'online')
{
    $defItem = 0;
    ob_start(); 
?>
        <div id="give-form" style="float: left">
        <select name="item" id="give-item">
        <?php foreach ($itemlist as $idx => $item): ?>
            <option id="item<?php echo $idx; ?>" value="<?php echo $idx; ?>" <?php if ($idx == $defItem) echo 'selected="selected"' ?>><?php echo CHtml::encode($item['name']) ?></option>
        <?php endforeach; ?>
        </select>
        <select name="amount" id="give-amount" style="width: auto">
        <?php for($i = 64; $i > 0; $i--): ?>
            <option value="<?php echo $i ?>" <?php if ($i == @$defAmount) echo 'selected="selected"' ?>><?php echo $i ?></option>
        <?php endfor; ?>
        </select>
        <?php echo CHtml::ajaxButton(Yii::t('mc', 'Give'), '', array('type'=>'POST',
                'data'=>array('ajax'=>'give', 'item'=>"js:$('#give-item').val()",
                Yii::app()->request->csrfTokenName=>Yii::app()->request->csrfToken,
                'amount'=>"js:$('#give-amount').val()"), 'success'=>'function(e) {if (e) alert(e);}')) ?>
        </div>

<?php
    $attribs[] = array('label'=>Yii::t('mc', 'Give'), 'type'=>'raw', 'value'=>ob_get_clean());
}

if (@$tp && $model->status == 'online')
{
    $attribs[] = array('label'=>Yii::t('mc', 'Teleport to'), 'type'=>'raw',
            'value'=>CHtml::tag('select', array('id'=>'tp-ajax'), $data['tp']).' '
            .CHtml::ajaxButton(Yii::t('mc', 'Teleport'), '', array('type'=>'POST',
                    'data'=>array('ajax'=>'tp', Yii::app()->request->csrfTokenName=>Yii::app()->request->csrfToken,
                    'player'=>"js:$('#tp-ajax').val()"), 'success'=>'function(e) {if (e) alert(e);}')));
}

if (@$summon && $model->status == 'online')
{
    $attribs[] = array('label'=>Yii::t('mc', 'Summon'), 'type'=>'raw',
            'value'=>CHtml::tag('select', array('id'=>'summon-ajax'), $data['summon']).' '
            .CHtml::ajaxButton(Yii::t('mc', 'Summon'), '', array('type'=>'POST',
                    'data'=>array('ajax'=>'summon', Yii::app()->request->csrfTokenName=>Yii::app()->request->csrfToken,
                    'player'=>"js:$('#summon-ajax').val()"))));
}


$this->widget('zii.widgets.CDetailView', array(
    'data'=>$model,
    'attributes'=>$attribs,
)); 

if ($edit)
    $this->endWidget();
?>

<?php if(Yii::app()->user->hasFlash('player')): ?>
<div class="flash-success">
    <?php echo Yii::app()->user->getFlash('player'); ?>
</div>
<?php endif ?>

<?php if (!$model->isNewRecord) $this->printRefreshScript(); ?>
