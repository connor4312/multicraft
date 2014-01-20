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
        'icon'=>'back',
    )
);
?>

<table class="stdtable">
<tr class="titlerow">
    <td>
        <div style="float: left; display: inline; margin-right: 10px" id="statusicon-ajax"><?php echo $data['statusicon'] ?></div><?php echo Yii::t('mc', 'Server') ?> <?php echo $command ? Yii::t('mc', 'Console') : Yii::t('mc', 'Log') ?>
    </td>
</tr>
<tr class="linerow">
    <td></td>
</tr>
<tr>
    <td>
        <?php if ($command): ?>
        <?php echo CHtml::beginForm() ?>
        <table class="stdtable" style="width: 100%">
        <tr>
            <td>
                <div style="display:none">
                    <input type="text" name="ieBugWorkaround"/>
                </div>
                <input type="text" id="command" name="command" value="" style="width: 100%"/>
            </td>
            <td>&nbsp;
                <?php echo CHtml::ajaxSubmitButton(Yii::t('mc', 'Send'), '', array('type'=>'POST',
                        'data'=>array('ajax'=>'command', Yii::app()->request->csrfTokenName=>Yii::app()->request->csrfToken,
                        'command'=>"js:$('#command').val()"), 'success'=>'js:command_response'
                    )) ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="flash-error" id="command-error" style="display: none"></div>
            </td>
        </tr>
        </table>
        <?php echo CHtml::endForm() ?>
        <?php endif ?>
        <!-- LOG -->
        <?php echo CHtml::textarea('log-ajax', $data['log'], array('class'=>'logArea', 'readonly'=>'readonly')) ?>
        <?php echo CHtml::ajaxLink(Yii::t('mc', 'Clear log'), '', array('type'=>'POST',
            'data'=>array('ajax'=>'clearLog', Yii::app()->request->csrfTokenName=>Yii::app()->request->csrfToken,),
            'success'=>'js:command_response')) ?>
    </td>
</tr>
</table>

<?php $this->printRefreshScript(); ?>
<?php echo CHtml::script('
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
        setTimeout(function() { refresh("log"); }, 500);
    }

    $(document).ready(function() {
        $("#command").focus();
    });'); ?>
