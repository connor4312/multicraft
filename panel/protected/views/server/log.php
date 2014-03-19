<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle = Yii::app()->name . ' - '.($command ? Yii::t('mc', 'Console') : Yii::t('mc', 'Log'));

$this->breadcrumbs=array(
    Yii::t('mc', 'Servers')=>array('index'),
    $model->name=>array('view', 'id'=>$model->id),
    $command ? Yii::t('mc', 'Console') : Yii::t('mc', 'Log'),
);

$this->menu = array(
    array(
        'label'=>Yii::t('mc', 'Back'),
        'url'=>array('server/view', 'id'=>$model->id),
        'icon'=>'arrow-left',
    )
);
?>

<div class="row">
    <h3>
        <div class="pull-left" id="statusicon-ajax"><?php echo $data['statusicon'] ?></div>
        <?php echo Yii::t('mc', 'Server') ?> <?php echo $command ? Yii::t('mc', 'Console') : Yii::t('mc', 'Log') ?>
    </h3>
</div>
<br>
<?php if ($command): ?>
<?php echo CHtml::beginForm() ?>
<div class="input-group">
    <input type="text" id="command" name="command" value="" class="form-control" data-focus>
    <span class="input-group-btn">
        <?php echo CHtml::ajaxSubmitButton(Yii::t('mc', 'Send'), '', array('type'=>'POST',
                'data'=>array('ajax'=>'command', Yii::app()->request->csrfTokenName=>Yii::app()->request->csrfToken,
                'command'=>"js:$('#command').val()"), 'success'=>'js:command_response'
            ), array('class' => 'btn btn-primary')) ?>
    </span>
</div>
<div class="alert alert-warning" id="command-error" style="display: none"></div>

<?php echo CHtml::endForm() ?>
<?php endif ?>
<!-- LOG -->
<div id="console" data-type="log"></div>
<?php echo CHtml::ajaxLink(Yii::t('mc', 'Clear log'), '', array('type'=>'POST',
    'data'=>array('ajax'=>'clearLog', Yii::app()->request->csrfTokenName=>Yii::app()->request->csrfToken,),
    'success'=>'js:command_response')) ?>

<?php $this->printRefreshScript(); ?>
<?php echo CHtml::script('
    scheduleRefresh(function(d){multicraft.console(d);});
    function command_response(data)
    {
        $("#command").focus();
        if (data)
        {
            $("#command-error").html(data)
            $("#command-error").show()
        }
        else
        {
            $("#command-error").hide()
            $("#command").val("")
        }
        setTimeout(function() { refresh("log", function(d){multicraft.console(d);});}, 500);
    }'); ?>
