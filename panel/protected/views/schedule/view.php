<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle = Yii::app()->name . ' - '.Yii::t('mc', 'View Task');

$this->breadcrumbs=array(
    Yii::t('mc', 'Servers')=>array('server/index'),
    $sv ? @Server::model()->findByPk((int)$sv)->name : Yii::t('mc', 'All')=>$sv ? array('server/view', 'id'=>$sv) : array('/server'),
    Yii::t('mc', 'Scheduled Tasks')=>$sv ? array('index', 'sv'=>$sv) : array('admin'),
    $model->isNewRecord ? Yii::t('mc', 'New Task') : CHtml::encode($model->name),
);

if (!$model->isNewRecord)
{
$this->menu=array(
    array(
        'label'=>Yii::t('mc', 'Delete Task'),
        'url'=>'#',
        'linkOptions'=>array(
            'submit'=>array('delete','id'=>$model->id),
            'confirm'=>Yii::t('mc', 'Are you sure you want to delete this task?'),
            'csrf'=>true,
        ),
        'icon'=>'schedule_del',
    ),
);
}

$this->menu[] = array(
    'label'=>Yii::t('mc', 'Back'),
    'url'=>($sv ? array('schedule/index', 'sv'=>$sv) : array('schedule/admin')),
    'icon'=>'back',
);

echo CHtml::css('#ival_do {width: auto;}');
echo CHtml::css('#ival_nr {min-width: 10px; display: none;}');
echo CHtml::css('#ival_type {min-width: 10px; display: none;}');

$form=$this->beginWidget('CActiveForm', array(
    'id'=>'user-form',
    'enableAjaxValidation'=>false,
    ));

$attribs = array();
if (Yii::app()->user->isSuperuser())
{
    $attribs[] = array('label'=>$form->labelEx($model,'server_id'), 'type'=>'raw',
        'value'=>$form->textField($model,'server_id').' '.$form->error($model,'server_id'),
        'hint'=>Yii::t('mc', 'The server ID, use 0 for "Global"'));
    $attribs[] = array('label'=>$form->labelEx($model,'hidden'), 'type'=>'raw',
            'value'=>$form->checkBox($model,'hidden')
                .' '.$form->error($model,'hidden'),
            'hint'=>Yii::t('mc', 'Only show this task to other superusers'));
}
$attribs[] = array('label'=>$form->labelEx($model,'name'), 'type'=>'raw',
    'value'=>$form->textField($model,'name').' '.$form->error($model,'name'));

if (!$model->isNewRecord)
{
    $attribs[] = array('label'=>$form->labelEx($model,'last_run_ts'), 'value'=>($model->last_run_ts ?
        @date(Yii::t('mc', 'd. M Y, H:i'), $model->last_run_ts) : Yii::t('mc', 'Never')).$form->error($model,'last_run_ts'));
    $attribs[] = array('label'=>$form->labelEx($model,'status'), 'value'=>
        Schedule::getStatusValues($model->status));
}
$attribs[] = array('label'=>$model->isNewRecord ? $form->labelEx($model,'status') : Yii::t('mc', 'Change status to'),
    'type'=>'raw', 'value'=>
    $form->dropDownList($model, 'status', array(0=>Schedule::getStatusValues(0), 3=>Schedule::getStatusValues(3)))
    .' '.$form->error($model,'status'));

$attribs[] = array('label'=>$form->labelEx($model,'scheduled_ts'), 'type'=>'raw', 'value'=>
    $this->widget('application.extensions.timepicker.EJuiDateTimePicker', array(
        'value'=>@date(Yii::t('mc', 'd. M Y H:i'), $model->scheduled_ts ? $model->scheduled_ts : time()),
        'name'=>'scheduled_ts',
        'options'=>array('dateFormat'=>Yii::t('mc', 'dd. M yy'),),
    ), true).' '.$form->error($model,'scheduled_ts'));

$attribs[] = array('name'=>'interval', 'type'=>'raw', 'value'=>
    CHtml::checkBox('ival_do', $model->interval > 0).' '.CHtml::dropDownList('ival_nr', $ival_nr, range(1, 59)).' '
    .CHtml::dropDownList('ival_type', $ival_type, array(Yii::t('mc', 'Minutes'), Yii::t('mc', 'Hours'), Yii::t('mc', 'Days'))));

$fnd = array('server_id'=>(int)$sv);
if (Command::model()->hasAttribute('hidden') && !Yii::app()->user->isSuperuser())
    $fnd['hidden'] = 0;
$attribs[] = array('name'=>$form->labelEx($model,'command'), 'type'=>'raw',
    'value'=>$form->dropDownList($model, 'command', array('0'=>Yii::t('mc', 'None')) + CHtml::listData(
        Command::model()->findAllByAttributes($fnd), 'id', 'name'))
    .' '.$form->error($model,'command'));

$attribs[] = array('name'=>$form->labelEx($model,'args'), 'type'=>'raw',
    'value'=>$form->textField($model, 'args').' '.$form->error($model,'command'),
    'hint'=>Yii::t('mc', 'For example the text for the say command.'));

$players = array(0=>Yii::t('mc', 'Server'), -1=>Yii::t('mc', 'Everyone'));
$cmd = Player::model()->getDbConnection()->createCommand('select `id`, `name` from `player` where `server_id`=?');
$plrs = $cmd->query(array(1=>$model->server_id));
foreach ($plrs as $pl)
    $players[$pl['id']] = $pl['name'];

$attribs[] = array('label'=>$form->labelEx($model,'run_for'), 'type'=>'raw',
    'value'=>$form->dropDownList($model, 'run_for', $players).' '.$form->error($model,'run_for'),
    'hint'=>Yii::t('mc', 'Runs only for players that are online, or always if "Server" is selected.'));

$attribs[] = array('label'=>'', 'type'=>'raw', 'value'=>CHtml::submitButton($model->isNewRecord ? Yii::t('mc', 'Create') : Yii::t('mc', 'Save')));

$this->widget('zii.widgets.CDetailView', array(
    'data'=>$model,
    'attributes'=>$attribs,
)); 

$this->endWidget();

echo CHtml::script('
function chIval(t)
{
    $("#ival_nr").toggle(t);
    $("#ival_type").toggle(t);
}
$("#ival_do").click(function() {
    chIval(this.checked);
});
$(function() { chIval($("#ival_do").is(":checked")); });
');

?>

<?php if(Yii::app()->user->hasFlash('schedule')): ?>
<div class="flash-success">
    <?php echo Yii::app()->user->getFlash('schedule'); ?>
</div>
<?php endif ?>
