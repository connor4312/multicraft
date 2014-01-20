<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle = Yii::app()->name . ' - '.Yii::t('mc', 'Chat');

$this->breadcrumbs=array(
    Yii::t('mc', 'Servers')=>array('index'),
    $model->name=>array('view', 'id'=>$model->id),
    Yii::t('mc', 'Chat'),
);

$this->menu = array(
    array(
        'label'=>Yii::t('mc', 'Back'),
        'url'=>array('server/view', 'id'=>$model->id),
        'icon'=>'back',
    )
);

?>

<table style="width: 100%" class="stdtable">
<tr class="titlerow"> 
    <td><?php echo Yii::t('mc', 'Connected players') ?></td>
    <td></td>
    <td><div style="float: left; display: inline; margin-right: 10px" id="statusicon-ajax"><?php echo $data['statusicon'] ?></div><?php echo Yii::t('mc', 'Chat') ?></td>
</tr>
<tr class="linerow">
    <td></td>
    <td style="width: 5px; background-color: transparent"></td>
    <td></td>
</tr>
<tr>
    <td style="width: 25%; vertical-align: top">
        <?php if ($getPlayers): ?>
        <!-- PLAYERS -->
        <table class="stdtable">
        <tbody id="players-ajax">
        <?php echo $data['players'] ?>
        </tbody>
        </table>
        <?php endif ?>
    </td>
    <td></td>
    <td>
        <?php if ($getChat): ?>
        <!-- CHAT -->
        <?php if ($chat): ?>
        <?php echo CHtml::beginForm() ?>
        <table class="stdtable" style="width: 100%">
        <tr>
            <td>
                <input type="text" id="message" name="message" value="" style="width: 100%"/>
                <div style="display:none">
                    <input type="text" name="ieBugWorkaround"/>
                </div>
            </td>
            <td>&nbsp;
                <?php echo CHtml::ajaxSubmitButton(Yii::t('mc', 'Send'), '', array('type'=>'POST',
                        'data'=>array('ajax'=>'chat', Yii::app()->request->csrfTokenName=>Yii::app()->request->csrfToken,
                        'message'=>"js:$('#message').val()"), 'success'=>'js:chat_response'
                    )) ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="flash-error" id="chat-error" style="display: none"></div>
            </td>
        </tr>
        </table>
        <?php echo CHtml::endForm() ?>
        <?php endif ?>
        <?php echo CHtml::textarea('chat-ajax', $data['chat'], array('class'=>'logArea', 'readonly'=>'readonly')) ?>
        <?php echo CHtml::ajaxLink(Yii::t('mc', 'Clear chat'), '', array('type'=>'POST',
                'data'=>array('ajax'=>'clearChat', Yii::app()->request->csrfTokenName=>Yii::app()->request->csrfToken,),
                'success'=>'js:chat_response')) ?>
        <?php endif ?>
    </td>
</tr>
</table>

<?php $this->printRefreshScript(); ?>
<?php echo CHtml::script('
    function chat_response(data)
    {
        $("#message").focus();
        if (data)
        {
            $("#chat-error").html(data)
            $("#chat-error").show()
        }
        else
        {
            $("#chat-error").hide()
            $("#message").val("")
        }
        setTimeout(function() { refresh("chat"); }, 500);
    }

    $(document).ready(function() {
        $("#message").focus();
    });'); ?>
