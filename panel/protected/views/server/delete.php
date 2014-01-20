<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->breadcrumbs=array(
    Yii::t('mc', 'Servers')=>array('index'),
    $model->name=>array('view', 'id'=>$model->id),
    Yii::t('mc', 'Delete')
);

Yii::app()->getClientScript()->registerCoreScript('jquery');

$this->menu=array(
    array(
        'label'=>Yii::t('mc', 'Back'),
        'url'=>array('server/view', 'id'=>$model->id),
        'icon'=>'back',
    ),
);
?>

<?php 

$form = $this->beginWidget('CActiveForm', array(
    'id'=>'server-form',
    'enableAjaxValidation'=>false,
));
echo CHtml::hiddenField('delete', 'true');

$owner = User::model()->findByPk((int)$model->owner);
$cons = McBridge::get()->conStrings();
$attribs = array('name',
    'owner'=>array('name'=>'owner', 'label'=>Yii::t('mc', 'Owner'), 'value'=>$owner ? $owner->name : '-'),
    'ip',
    'port',
    'daemon_id'=>array('name'=>'daemon_id',
        'value'=>(isset($cons[$model->daemon_id]) ? $cons[$model->daemon_id] : Yii::t('mc', 'local host'))),
    'dir',
);

if ($canDel)
{
    $value = Yii::t('mc', 'Type <b>yes</b> here to delete <b>all</b> files in this servers base directory (including all worlds).').($info ? Yii::t('mc', 'The associated database will also be deleted.') : '')
        .'<br/>'.CHtml::textField('del_files', Yii::t('mc', 'no'))
        .'<span class="hint"></span>';
}
else
{
    $value = Yii::t('mc', 'Server files won\'t be deleted:').'<br/>';

    if ($status == 'error')
        $value .= Yii::t('mc', 'There is an error with the daemon connection for this Server').'<br/>';
    else if ($status != 'stopped')
        $value .= Yii::t('mc', 'The server is still running, please stop the server first.').'<br/>';
    $value .= '<br/>';

    if (count($shared) > 1)
    {
        $value .= Yii::t('mc', 'Base directory shared with the following servers:').'<br/>';
        $value .= '<ul>';
        foreach ($shared as $s)
            if ($s->id != $model->id)
                $value .= '<li>'.CHtml::link(CHtml::encode($s->name), array('view', 'id'=>$s->id)).'</li>';
        $value .= '</ul>';
    }
}
$value .= '<br/><span style="font-size: 10px">'.Yii::t('mc', 'Note that if the base directory doesn\'t get deleted and you\'re using a multiuser configuration the system user/group won\'t be deleted either').'</span>';
$attribs[] = array('label'=>Yii::t('mc', 'Files'), 'type'=>'raw', 'value'=>$value);

if (count($owned) <= 1)
{
    $value = CHtml::checkBox('del_user', false, array('style'=>'width: auto')).' '.CHtml::label(Yii::t('mc', 'Delete Control Panel User'), 'del_user')
        .'<span class="hint"></span>';
}
else
{
    $value = Yii::t('mc', 'Can\'t delete server owner, the user still owns other servers:').'<br/>';
    $value .= '<ul>';
    foreach ($owned as $s)
        if ($s->id != $model->id)
            $value .= '<li>'.CHtml::link(CHtml::encode($s->name), array('view', 'id'=>$s->id)).'</li>';
    $value .= '</ul>';
}
$usr = User::model()->findByPk($model->owner);
if ($usr && $usr->global_role != 'superuser' && $usr->name != Yii::app()->user->superuser)
    $attribs[] = array('label'=>Yii::t('mc', 'User'), 'type'=>'raw', 'value'=>$value);
    
$attribs[] = array('label'=>'', 'type'=>'raw', 'value'=>CHtml::submitButton(Yii::t('mc', 'Delete')));

$this->widget('zii.widgets.CDetailView', array(
    'data'=>$model,
    'itemTemplate'=>"<tr class=\"{class}\"><th style=\"width: 30%\">{label}</th><td style=\"width: 70%\">{value}</td></tr>\n",
    'attributes'=>$attribs,
));

$this->endWidget();
?>

<?php if(Yii::app()->user->hasFlash('server')): ?>
<div class="flash-error">
    <?php echo Yii::app()->user->getFlash('server'); ?>
</div>
<?php endif ?>


